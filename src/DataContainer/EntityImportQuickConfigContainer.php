<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\DataContainer;

use Contao\Controller;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\Database;
use Contao\DataContainer;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\EntityImportBundle\Event\BeforeImportEvent;
use HeimrichHannot\EntityImportBundle\Event\BeforeItemImportEvent;
use HeimrichHannot\EntityImportBundle\Importer\ImporterFactory;
use HeimrichHannot\EntityImportBundle\Source\SourceFactory;
use HeimrichHannot\EntityImportBundle\Source\SourceInterface;
use HeimrichHannot\ListWidget\ListWidget;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EntityImportQuickConfigContainer
{
    protected DatabaseUtil  $databaseUtil;
    protected SourceFactory $sourceFactory;
    protected EventDispatcherInterface $eventDispatcher;
    protected ModelUtil $modelUtil;
    protected Request $request;
    protected ImporterFactory $importerFactory;
    protected UrlUtil $urlUtil;
    protected DcaUtil $dcaUtil;

    public function __construct(
        ModelUtil $modelUtil,
        EventDispatcherInterface $eventDispatcher,
        Request $request,
        ImporterFactory $importerFactory,
        UrlUtil $urlUtil,
        DcaUtil $dcaUtil,
        DatabaseUtil $databaseUtil,
        SourceFactory $sourceFactory
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->modelUtil = $modelUtil;
        $this->request = $request;
        $this->importerFactory = $importerFactory;
        $this->urlUtil = $urlUtil;
        $this->dcaUtil = $dcaUtil;
        $this->databaseUtil = $databaseUtil;
        $this->sourceFactory = $sourceFactory;
    }

    public function getImporterConfigs()
    {
        $options = [];

        if (null === ($configs = $this->modelUtil->findAllModelInstances('tl_entity_import_config', [
                'order' => 'tl_entity_import_config.title ASC',
            ]))) {
            return [];
        }

        while ($configs->next()) {
            $options[$configs->id] = $configs->title;
        }

        return $options;
    }

    public function getDryRunOperation($row, $href, $label, $title, $icon, $attributes)
    {
        if (null !== ($config = $this->modelUtil->findModelInstanceByPk('tl_entity_import_config', $row['importerConfig']))) {
            if ($config->useCronInWebContext) {
                return '';
            }
        }

        return '<a href="'.Controller::addToUrl($href.'&amp;id='.$row['id']).'&rt='.\RequestToken::get().'" title="'.\StringUtil::specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label).'</a> ';
    }

    public function modifyDca(DataContainer $dc)
    {
        if (null === ($quickImporter = $this->modelUtil->findModelInstanceByPk('tl_entity_import_quick_config', $dc->id)) || !$quickImporter->importerConfig) {
            return;
        }

        if (null === ($importer = $this->modelUtil->findModelInstanceByPk('tl_entity_import_config', $quickImporter->importerConfig))) {
            return;
        }

        if (null === ($sourceModel = $this->modelUtil->findModelInstanceByPk('tl_entity_import_source', $importer->pid))) {
            return;
        }

        $dca = &$GLOBALS['TL_DCA']['tl_entity_import_quick_config'];

        $this->dcaUtil->loadDc($importer->targetTable);

        $targetDca = &$GLOBALS['TL_DCA'][$importer->targetTable];

        if (EntityImportConfigContainer::STATE_READY_FOR_IMPORT === $importer->state) {
            $dca['palettes']['default'] = '{general_legend},importProgress;';

            return;
        }

        if (EntityImportSourceContainer::RETRIEVAL_TYPE_CONTAO_FILE_SYSTEM === $sourceModel->retrievalType) {
            switch ($sourceModel->fileType) {
                case EntityImportSourceContainer::FILETYPE_CSV:
                    $dca['palettes']['default'] = str_replace('importerConfig', 'importerConfig,fileSRC,csvHeaderRow,csvPreviewList', $dca['palettes']['default']);

                    if (isset($targetDca['config']['ptable']) && $targetDca['config']['ptable'] && Database::getInstance()->fieldExists('pid', $importer->targetTable)) {
                        $dca['palettes']['default'] = str_replace('fileSRC', 'fileSRC,parentEntity', $dca['palettes']['default']);
                    }

                    // large dataset? -> ajax list
                    if ($importer->useCacheForQuickImporters) {
                        unset($dca['fields']['csvPreviewList']['eval']['listWidget']['items_callback']);

                        $dca['fields']['csvPreviewList']['eval']['listWidget']['ajax'] = true;
                        $dca['fields']['csvPreviewList']['eval']['listWidget']['table'] = 'tl_entity_import_cache';
                        $dca['fields']['csvPreviewList']['eval']['listWidget']['ajaxConfig'] = [
                            'load_items_callback' => [self::class, 'loadCsvRowsFromCache'],
                            'prepare_items_callback' => [self::class, 'prepareCachedCsvRows'],
                        ];
                    }

                    break;
            }
        }
    }

    public function loadCsvRowsFromCache($config, $options = [], $context = null, $dc = null)
    {
        $options = [
            'table' => $config['table'],
            'columns' => $config['columns'],
            // filtering
            'column' => [$config['table'].'.cache_ptable = ?', $config['table'].'.cache_pid = ?'],
            'value' => ['tl_entity_import_quick_config', $dc->id],
        ];

        return ListWidget::loadItems($config, $options, $context, $dc);
    }

    public function cacheCsvRows(DataContainer $dc)
    {
        // cache might be invalid now -> delete tl_md_recipient
        $this->databaseUtil->delete('tl_entity_import_cache', 'cache_ptable=? AND cache_pid=?', ['tl_entity_import_quick_config', $dc->id]);

        // cache the rows
        if (null === ($quickImporter = $this->modelUtil->findModelInstanceByPk('tl_entity_import_quick_config', $dc->id)) ||
            !$quickImporter->importerConfig || !$quickImporter->fileSRC) {
            return;
        }

        if (null === ($importerConfig = $this->modelUtil->findModelInstanceByPk('tl_entity_import_config', $quickImporter->importerConfig))) {
            return;
        }

        if (null === ($sourceModel = $this->modelUtil->findModelInstanceByPk('tl_entity_import_source', $importerConfig->pid))) {
            return;
        }

        if (EntityImportSourceContainer::RETRIEVAL_TYPE_CONTAO_FILE_SYSTEM !== $sourceModel->retrievalType ||
            EntityImportSourceContainer::FILETYPE_CSV !== $sourceModel->fileType) {
            return;
        }

        $this->addParentEntityToFieldMapping($quickImporter, $importerConfig);
        $sourceModel->fileSRC = $quickImporter->fileSRC;

        $importer = $this->importerFactory->createInstance($importerConfig, [
            'sourceModel' => $sourceModel,
        ]);

        /**
         * @var SourceInterface
         */
        $source = $this->sourceFactory->createInstance($sourceModel->id);

        // set domain
        $source->setDomain($importerConfig->cronDomain);

        $items = $importer->getMappedItems();

        $event = $this->eventDispatcher->dispatch(new BeforeImportEvent($items, $importerConfig, $importer, $source, true), BeforeImportEvent::NAME);

        $items = $event->getItems();

        if ($quickImporter->csvHeaderRow) {
            unset($items[0]);
        }

        $itemsToInsert = [];

        foreach ($items as $item) {
            // call the event (else db constraints might fail)
            /** @var BeforeItemImportEvent $event */
            $event = $this->eventDispatcher->dispatch(new BeforeItemImportEvent(
                $item,
                $item,
                $importerConfig,
                $importer,
                $source,
                false,
                true
            ), BeforeItemImportEvent::NAME);

            $itemsToInsert[] = $event->getMappedItem();
        }

        $this->databaseUtil->doBulkInsert('tl_entity_import_cache', $itemsToInsert, [
            'cache_ptable' => 'tl_entity_import_quick_config',
            'cache_pid' => $dc->id,
        ]);
    }

    public function prepareCachedCsvRows($items, $config, $options = [], $context = null, $dc = null): array
    {
        $itemData = [];

        if (!$items) {
            return $itemData;
        }

        while ($items->next()) {
            $itemModel = $items->current();
            $item = [];

            foreach ($config['columns'] as $key => $column) {
                $item[] = [
                    'value' => $itemModel->{$column['db']},
                ];
            }

            $itemData[] = $item;
        }

        return $itemData;
    }

    public function getParentEntitiesAsOptions(\Contao\DataContainer $dc)
    {
        if (null === ($quickImporter = $this->modelUtil->findModelInstanceByPk('tl_entity_import_quick_config', $dc->id)) || !$quickImporter->importerConfig) {
            return [];
        }

        if (null === ($importer = $this->modelUtil->findModelInstanceByPk('tl_entity_import_config', $quickImporter->importerConfig))) {
            return [];
        }

        $dca = &$GLOBALS['TL_DCA'][$importer->targetTable];

        if (!isset($dca['config']['ptable'])) {
            return [];
        }

        return System::getContainer()->get('huh.utils.choice.model_instance')->getCachedChoices([
            'dataContainer' => $dca['config']['ptable'],
        ]);
    }

    public function import()
    {
        $this->runImport(false);
    }

    public function dryRun()
    {
        $this->runImport(true);
    }

    public function getHeaderFieldsForPreview($config, $widget, \DataContainer $dc)
    {
        if (null === ($quickImporter = $this->modelUtil->findModelInstanceByPk('tl_entity_import_quick_config', $dc->id)) || !$quickImporter->importerConfig) {
            return [];
        }

        if (null === ($importer = $this->modelUtil->findModelInstanceByPk('tl_entity_import_config', $quickImporter->importerConfig))) {
            return [];
        }

        $fields = [];

        foreach (StringUtil::deserialize($importer->fieldMapping, true) as $mapping) {
            $fields[$mapping['columnName']] = $this->dcaUtil->getLocalizedFieldName($mapping['columnName'], $importer->targetTable);
        }

        if (Database::getInstance()->fieldExists('pid', $importer->targetTable) && $quickImporter->parentEntity) {
            $this->dcaUtil->loadDc($importer->targetTable);

            $dca = &$GLOBALS['TL_DCA'][$importer->targetTable];

            if (isset($dca['config']['ptable'])) {
                $fields = array_merge(['pid' => $this->dcaUtil->getLocalizedFieldName('pid', $importer->targetTable)], $fields);
            }
        }

        return $fields;
    }

    public function getItemsForPreview($config, $widget, $dc)
    {
        if (null === ($quickImporter = $this->modelUtil->findModelInstanceByPk('tl_entity_import_quick_config', $dc->id)) || !$quickImporter->importerConfig || !$quickImporter->fileSRC) {
            return [];
        }

        if (null === ($importer = $this->modelUtil->findModelInstanceByPk('tl_entity_import_config', $quickImporter->importerConfig))) {
            return [];
        }

        if (null === ($sourceModel = $this->modelUtil->findModelInstanceByPk('tl_entity_import_source', $importer->pid))) {
            return [];
        }

        $this->addParentEntityToFieldMapping($quickImporter, $importer);
        $sourceModel->fileSRC = $quickImporter->fileSRC;

        $importer = $this->importerFactory->createInstance($importer, [
            'sourceModel' => $sourceModel,
        ]);

        $result = $importer->getMappedItems();

        if ($quickImporter->csvHeaderRow) {
            unset($result[0]);
        }

        return $result;
    }

    protected function addParentEntityToFieldMapping($quickImporter, $importer)
    {
        if (!Database::getInstance()->fieldExists('pid', $importer->targetTable) || !$quickImporter->parentEntity) {
            return;
        }

        $this->dcaUtil->loadDc($importer->targetTable);

        $dca = &$GLOBALS['TL_DCA'][$importer->targetTable];

        if (!isset($dca['config']['ptable'])) {
            return;
        }

        $mapping = array_merge([[
            'columnName' => 'pid',
            'valueType' => 'static_value',
            'staticValue' => $quickImporter->parentEntity,
        ]], StringUtil::deserialize($importer->fieldMapping, true));

        $importer->fieldMapping = serialize($mapping);
    }

    private function runImport(bool $dry = false)
    {
        $config = $this->request->getGet('id');

        if (null === ($quickImporter = $this->modelUtil->findModelInstanceByPk('tl_entity_import_quick_config', $config)) || !$quickImporter->importerConfig) {
            return;
        }

        if (null === ($importerConfig = $this->modelUtil->findModelInstanceByPk('tl_entity_import_config', $quickImporter->importerConfig))) {
            return;
        }

        if (null === ($sourceModel = $this->modelUtil->findModelInstanceByPk('tl_entity_import_source', $importerConfig->pid))) {
            return;
        }

        $this->addParentEntityToFieldMapping($quickImporter, $importerConfig);
        $sourceModel->fileSRC = $quickImporter->fileSRC;
        $sourceModel->csvHeaderRow = $quickImporter->csvHeaderRow;

        $importer = $this->importerFactory->createInstance($importerConfig, [
            'sourceModel' => $sourceModel,
        ]);

        if ($importerConfig->useCronInWebContext) {
            $importerConfig->importStarted = $importerConfig->importProgressCurrent = $importerConfig->importProgressTotal = $importerConfig->importProgressSkipped = 0;
            $importerConfig->state = EntityImportConfigContainer::STATE_READY_FOR_IMPORT;
            $importerConfig->importProgressResult = '';
            $importerConfig->save();

            throw new RedirectResponseException($this->urlUtil->addQueryString('act=edit', $this->urlUtil->removeQueryString(['key'])));
        }
        $importer->setDryRun($dry);
        $result = $importer->run();
        $importer->outputFinalResultMessage($result);

        throw new RedirectResponseException($this->urlUtil->removeQueryString(['key', 'id']));
    }
}
