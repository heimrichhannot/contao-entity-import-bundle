<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\DataContainer;

use Contao\Controller;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\DataContainer;
use Contao\DcaLoader;
use Contao\Image;
use Contao\Input;
use Contao\Model;
use Contao\StringUtil;
use Contao\System;
use Doctrine\DBAL\Connection;
use HeimrichHannot\EntityImportBundle\Event\BeforeImportEvent;
use HeimrichHannot\EntityImportBundle\Event\BeforeItemImportEvent;
use HeimrichHannot\EntityImportBundle\Importer\ImporterFactory;
use HeimrichHannot\EntityImportBundle\Source\SourceFactory;
use HeimrichHannot\EntityImportBundle\Source\SourceInterface;
use HeimrichHannot\EntityImportBundle\Util\EntityImportUtil;
use HeimrichHannot\ListWidgetBundle\Widget\ListWidget;
use HeimrichHannot\UtilsBundle\StaticUtil\StaticArrayUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EntityImportQuickConfigContainer
{
    const ON_DUPLICATE_KEY_IGNORE = 'IGNORE';
    const ON_DUPLICATE_KEY_UPDATE = 'UPDATE';

    protected SourceFactory            $sourceFactory;
    protected EventDispatcherInterface $eventDispatcher;
    protected ImporterFactory          $importerFactory;
    protected Utils                    $utils;
    protected ContaoFramework          $framework;
    protected Connection               $connection;
    protected EntityImportUtil         $entityImportUtil;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ImporterFactory $importerFactory,
        SourceFactory $sourceFactory,
        ContaoFramework $framework,
        Connection $connection,
        EntityImportUtil $entityImportUtil,
        Utils $utils
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->importerFactory = $importerFactory;
        $this->sourceFactory = $sourceFactory;
        $this->utils = $utils;
        $this->framework = $framework;
        $this->connection = $connection;
        $this->entityImportUtil = $entityImportUtil;
    }

    public function getImporterConfigs()
    {
        $options = [];

        if (null === ($configs = $this->utils->model()->findModelInstancesBy('tl_entity_import_config', [], [], [
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
        if (null !== ($config = $this->utils->model()->findModelInstanceByPk('tl_entity_import_config', $row['importerConfig']))) {
            if ($config->useCronInWebContext) {
                return '';
            }
        }

        return '<a data-turbo="false" href="'.Controller::addToUrl($href.'&amp;id='.$row['id']).'&rt='.\Contao\System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue().'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
    }

    public function modifyDca(DataContainer $dc)
    {
        if (!$dc->id) {
            return;
        }

        if (null === ($quickImporter = $this->utils->model()->findModelInstanceByPk('tl_entity_import_quick_config', $dc->id)) || !$quickImporter->importerConfig) {
            return;
        }

        if (null === ($importer = $this->utils->model()->findModelInstanceByPk('tl_entity_import_config', $quickImporter->importerConfig))) {
            return;
        }

        if (null === ($sourceModel = $this->utils->model()->findModelInstanceByPk('tl_entity_import_source', $importer->pid))) {
            return;
        }

        $dca = &$GLOBALS['TL_DCA']['tl_entity_import_quick_config'];

        $loader = new DcaLoader($importer->targetTable);
        $loader->load();

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
        $this->connection->delete('tl_entity_import_cache', ['cache_ptable' => 'tl_entity_import_quick_config', 'cache_pid' => $dc->id]);

        // cache the rows
        if (null === ($quickImporter = $this->utils->model()->findModelInstanceByPk('tl_entity_import_quick_config', $dc->id)) ||
            !$quickImporter->importerConfig || !$quickImporter->fileSRC) {
            return;
        }

        if (null === ($importerConfig = $this->utils->model()->findModelInstanceByPk('tl_entity_import_config', $quickImporter->importerConfig))) {
            return;
        }

        if (null === ($sourceModel = $this->utils->model()->findModelInstanceByPk('tl_entity_import_source', $importerConfig->pid))) {
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

        $this->doBulkInsert('tl_entity_import_cache', $itemsToInsert, [
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

    public function getParentEntitiesAsOptions(DataContainer $dc): array
    {
        if (!$dc->id) {
            return [];
        }

        if (null === ($quickImporter = $this->utils->model()->findModelInstanceByPk('tl_entity_import_quick_config', $dc->id)) || !$quickImporter->importerConfig) {
            return [];
        }

        if (null === ($importer = $this->utils->model()->findModelInstanceByPk('tl_entity_import_config', $quickImporter->importerConfig))) {
            return [];
        }

        $dca = &$GLOBALS['TL_DCA'][$importer->targetTable];

        if (!isset($dca['config']['ptable'])) {
            return [];
        }

        $options = [];

        $models = $this->utils->model()->findModelInstancesBy($dca['config']['ptable'], [], []);

        if (null !== $models) {
            while ($models->next()) {
                $options[$models->id] = $models->title ?: $models->headline ?: $models->id;
            }
        }

        return $options;
    }

    public function import()
    {
        $this->runImport(false);
    }

    public function dryRun()
    {
        $this->runImport(true);
    }

    public function getHeaderFieldsForPreview($config, $widget, DataContainer $dc)
    {
        if (null === ($quickImporter = $this->utils->model()->findModelInstanceByPk('tl_entity_import_quick_config', $dc->id)) || !$quickImporter->importerConfig) {
            return [];
        }

        if (null === ($importer = $this->utils->model()->findModelInstanceByPk('tl_entity_import_config', $quickImporter->importerConfig))) {
            return [];
        }

        $fields = [];

        foreach (StringUtil::deserialize($importer->fieldMapping, true) as $mapping) {
            $fields[$mapping['columnName']] = $this->entityImportUtil->getLocalizedFieldName($mapping['columnName'], $importer->targetTable);
        }

        if (Database::getInstance()->fieldExists('pid', $importer->targetTable) && $quickImporter->parentEntity) {
            $loader = new DcaLoader($importer->targetTable);
            $loader->load();

            $dca = &$GLOBALS['TL_DCA'][$importer->targetTable];

            if (isset($dca['config']['ptable'])) {
                $fields = array_merge(['pid' => $this->entityImportUtil->getLocalizedFieldName('pid', $importer->targetTable)], $fields);
            }
        }

        return $fields;
    }

    public function getItemsForPreview($config, $widget, $dc)
    {
        if (null === ($quickImporter = $this->utils->model()->findModelInstanceByPk('tl_entity_import_quick_config', $dc->id)) || !$quickImporter->importerConfig || !$quickImporter->fileSRC) {
            return [];
        }

        if (null === ($importer = $this->utils->model()->findModelInstanceByPk('tl_entity_import_config', $quickImporter->importerConfig))) {
            return [];
        }

        if (null === ($sourceModel = $this->utils->model()->findModelInstanceByPk('tl_entity_import_source', $importer->pid))) {
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

        $loader = new DcaLoader($importer->targetTable);
        $loader->load();

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
        $config = Input::get('id');

        if (null === ($quickImporter = $this->utils->model()->findModelInstanceByPk('tl_entity_import_quick_config', $config)) || !$quickImporter->importerConfig) {
            return;
        }

        if (null === ($importerConfig = $this->utils->model()->findModelInstanceByPk('tl_entity_import_config', $quickImporter->importerConfig))) {
            return;
        }

        if (null === ($sourceModel = $this->utils->model()->findModelInstanceByPk('tl_entity_import_source', $importerConfig->pid))) {
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

            throw new RedirectResponseException($this->utils->url()->addQueryStringParameterToUrl('act=edit', $this->utils->url()->removeQueryStringParameterFromUrl('key')));
        }
        $importer->setDryRun($dry);
        $result = $importer->run();
        $importer->outputFinalResultMessage($result);

        $url = $this->utils->url()->removeQueryStringParameterFromUrl('key');
        $url = $this->utils->url()->removeQueryStringParameterFromUrl('id', $url);

        throw new RedirectResponseException($url);
    }

    /**
     * Bulk insert SQL of given data.
     *
     * @param string   $table          The database table, where new items should be stored inside
     * @param array    $data           An array of values associated to its field
     * @param array    $fixedValues    A array of fixed values associated to its field that should be set for each row as fixed values
     * @param mixed    $onDuplicateKey null = Throw error on duplicates, self::ON_DUPLICATE_KEY_IGNORE = ignore error duplicates (skip this entries),
     *                                 self::ON_DUPLICATE_KEY_UPDATE = update existing entries
     * @param callable $callback       A callback that should be triggered after each cycle, contains $arrValues of current cycle
     * @param callable $itemCallback   A callback to change the insert values for each items, contains $arrValues as first argument, $arrFields as
     *                                 second, $arrOriginal as third, expects an array as return value with same order as $arrFields, if no array is
     *                                 returned, insert of the row will be skipped item insert
     * @param int      $bulkSize       The bulk size
     * @param string   $pk             The primary key of the current table (default: id)
     */
    public function doBulkInsert(
        string $table,
        array $data = [],
        array $fixedValues = [],
        $onDuplicateKey = null,
        $callback = null,
        $itemCallback = null,
        int $bulkSize = 100,
        string $pk = 'id'
    ) {
        /** @var Database $database */
        $database = $this->framework->createInstance(Database::class);

        if (!$database->tableExists($table) || empty($data)) {
            return null;
        }

        $fields = $database->getFieldNames($table, true);
        StaticArrayUtil::removeValue($pk, $fields); // unset id
        $fields = array_values($fields);

        $bulkSize = (int) $bulkSize;

        $query = '';
        $duplicateKey = '';
        $startQuery = sprintf('INSERT %s INTO %s (%s) VALUES ', self::ON_DUPLICATE_KEY_IGNORE === $onDuplicateKey ? 'IGNORE' : '', $table, implode(',', $fields));

        if (self::ON_DUPLICATE_KEY_UPDATE === $onDuplicateKey) {
            $duplicateKey = ' ON DUPLICATE KEY UPDATE '.implode(
                    ',',
                    array_map(
                        function ($val) {
                            // escape double quotes
                            return $val.' = VALUES('.$val.')';
                        },
                        $fields
                    )
                );
        }

        $i = 0;

        $columnWildcards = array_map(
            function ($val) {
                return '?';
            },
            $fields
        );

        foreach ($data as $key => $varData) {
            if (0 === $i) {
                $values = [];
                $return = [];
                $query = $startQuery;
            }

            $columns = $columnWildcards;

            if ($varData instanceof Model) {
                $varData = $varData->row();
            }

            foreach ($fields as $n => $strField) {
                $varValue = isset($varData[$strField]) ? $varData[$strField] : 'DEFAULT';

                if (\in_array($strField, array_keys($fixedValues))) {
                    $varValue = $fixedValues[$strField];
                }

                // replace SQL Keyword DEFAULT within wildcards ?
                if ('DEFAULT' === $varValue) {
                    $columns[$n] = 'DEFAULT';

                    continue;
                }

                $return[$i][$strField] = $varValue;
            }

            // manipulate the item
            if (\is_callable($itemCallback)) {
                if (!isset($return[$i])) {
                    continue;
                }
                $varCallback = \call_user_func_array($itemCallback, [$return[$i], $fields, $varData]);

                if (!\is_array($varCallback)) {
                    continue;
                }

                foreach ($fields as $n => $strField) {
                    $varValue = isset($varCallback[$strField]) ? $varCallback[$strField] : 'DEFAULT';

                    // replace SQL Keyword DEFAULT within wildcards ?
                    if ('DEFAULT' === $varValue) {
                        $columns[$n] = 'DEFAULT';

                        continue;
                    }

                    $columns[$n] = '?';
                    $return[$i][$strField] = $varValue;
                }
            }

            // add values to insert array
            $values = array_merge($values, array_values($return[$i]));

            $query .= '('.implode(',', $columns).'),';

            ++$i;

            if ($bulkSize === $i) {
                $query = rtrim($query, ',');

                if (self::ON_DUPLICATE_KEY_UPDATE === $onDuplicateKey) {
                    $query .= $duplicateKey;
                }

                $database->prepare($query)->execute($values);

                if (\is_callable($callback)) {
                    \call_user_func_array($callback, [$return]);
                }

                $query = '';

                $i = 0;
            }
        }

        // remaining elements < $intBulkSize
        if ($query) {
            $query = rtrim($query, ',');

            if (self::ON_DUPLICATE_KEY_UPDATE === $onDuplicateKey) {
                $query .= $duplicateKey;
            }

            $database->prepare($query)->execute($values);

            if (\is_callable($callback)) {
                \call_user_func_array($callback, [$return]);
            }
        }
    }
}
