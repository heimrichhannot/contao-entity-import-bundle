<?php

namespace HeimrichHannot\EntityImportBundle\EventListener\DataContainer;

use Contao\Image;
use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\DataContainer;
use Contao\StringUtil;
use Contao\System;
use Doctrine\DBAL\Connection;
use HeimrichHannot\EntityImportBundle\Event\BeforeImportEvent;
use HeimrichHannot\EntityImportBundle\Event\BeforeItemImportEvent;
use HeimrichHannot\EntityImportBundle\Importer\ImporterFactory;
use HeimrichHannot\EntityImportBundle\Source\SourceFactory;
use HeimrichHannot\EntityImportBundle\Source\SourceInterface;
use HeimrichHannot\ListWidgetBundle\Widget\ListWidget;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EntityImportQuickConfigContainer
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly Utils $utils,
        private readonly Connection $conn,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ImporterFactory $importerFactory,
        private readonly SourceFactory $sourceFactory,
        private readonly ContaoFramework $framework
    ) {
    }

    #[AsCallback('tl_entity_import_quick_config', 'fields.importerConfig.options')]
    public function getImporterConfigs(): array
    {
        $options = [];

        if (null === ($configs = $this->utils->model()->findAllModelInstances('tl_entity_import_config', [
                'order' => 'tl_entity_import_config.title ASC',
            ]))) {
            return [];
        }

        while ($configs->next()) {
            $options[$configs->id] = $configs->title;
        }

        return $options;
    }

    #[AsCallback('tl_entity_import_quick_config', 'list.operations.dryRun.button')]
    public function getDryRunOperation($row, $href, $label, $title, $icon, $attributes): string
    {
        if (null !== ($config = $this->utils->model()->findModelInstanceByPk('tl_entity_import_config', $row['importerConfig']))) {
            if ($config->useCronInWebContext) {
                return '';
            }
        }

        return '<a href="'.Controller::addToUrl($href.'&amp;id='.$row['id']).'&rt='.System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue().'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
    }

    #[AsCallback('tl_entity_import_quick_config', 'config.onload')]
    public function modifyDca(DataContainer $dc): void
    {
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

        $this->framework->getAdapter(Controller::class)->loadDataContainer($importer->targetTable);

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
            'column' => [$config['table'].'.cache_ptable = ?', $config['table'].'.cache_pid = ?'],
            'value' => ['tl_entity_import_quick_config', $dc->id],
        ];

        return ListWidget::loadItems($config, $options, $context, $dc);
    }

    #[AsCallback('tl_entity_import_quick_config', 'config.onsubmit')]
    public function cacheCsvRows(DataContainer $dc): void
    {
        $this->conn->executeStatement(
            'DELETE FROM tl_entity_import_cache WHERE cache_ptable = ? AND cache_pid = ?',
            ['tl_entity_import_quick_config', $dc->id]
        );

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

        $source = $this->sourceFactory->createInstance($sourceModel->id);
        $source->setDomain($importerConfig->cronDomain);

        $items = $importer->getMappedItems();

        $event = $this->eventDispatcher->dispatch(new BeforeImportEvent($items, $importerConfig, $importer, $source, true), BeforeImportEvent::NAME);
        $items = $event->getItems();

        if ($quickImporter->csvHeaderRow) {
            unset($items[0]);
        }

        $itemsToInsert = [];

        foreach ($items as $item) {
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

        $this->bulkInsertCacheRows($itemsToInsert, (int) $dc->id);
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

    private function getLocalizedFieldName(string $field, string $table): string
    {
        $this->framework->getAdapter(Controller::class)->loadDataContainer($table);

        $label = $GLOBALS['TL_DCA'][$table]['fields'][$field]['label'][0] ?? null;

        return $label ?: $field;
    }

    private function bulkInsertCacheRows(array $items, int $parentId): void
    {
        if (empty($items)) {
            return;
        }

        $defaults = [
            'cache_ptable' => 'tl_entity_import_quick_config',
            'cache_pid' => $parentId,
        ];

        foreach ($items as $item) {
            $this->conn->insert('tl_entity_import_cache', array_merge($defaults, $item));
        }
    }

    private function getCurrentQuery(): array
    {
        $request = $this->requestStack->getCurrentRequest();

        return $request?->query->all() ?? [];
    }

    private function buildUrl(array $query): string
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return '';
        }

        $path = $request->getBaseUrl().$request->getPathInfo();

        return $path.($query ? '?'.http_build_query($query) : '');
    }

    #[AsCallback('tl_entity_import_quick_config', 'fields.parentEntity.options')]
    public function getParentEntitiesAsOptions(DataContainer $dc): array
    {
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

        return System::getContainer()->get('huh.utils.choice.model_instance')->getCachedChoices([
            'dataContainer' => $dca['config']['ptable'],
        ]);
    }

    public function import(): void
    {
        $this->runImport(false);
    }

    public function dryRun(): void
    {
        $this->runImport(true);
    }

    public function getHeaderFieldsForPreview($config, $widget, DataContainer $dc): array
    {
        if (null === ($quickImporter = $this->utils->model()->findModelInstanceByPk('tl_entity_import_quick_config', $dc->id)) || !$quickImporter->importerConfig) {
            return [];
        }

        if (null === ($importer = $this->utils->model()->findModelInstanceByPk('tl_entity_import_config', $quickImporter->importerConfig))) {
            return [];
        }

        $fields = [];

        foreach (StringUtil::deserialize($importer->fieldMapping, true) as $mapping) {
            $fields[$mapping['columnName']] = $this->getLocalizedFieldName($mapping['columnName'], $importer->targetTable);
        }

        if (Database::getInstance()->fieldExists('pid', $importer->targetTable) && $quickImporter->parentEntity) {
            $this->framework->getAdapter(Controller::class)->loadDataContainer($importer->targetTable);

            $dca = &$GLOBALS['TL_DCA'][$importer->targetTable];

            if (isset($dca['config']['ptable'])) {
                $fields = array_merge(['pid' => $this->getLocalizedFieldName('pid', $importer->targetTable)], $fields);
            }
        }

        return $fields;
    }

    public function getItemsForPreview($config, $widget, $dc): array
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

    protected function addParentEntityToFieldMapping($quickImporter, $importer): void
    {
        if (!Database::getInstance()->fieldExists('pid', $importer->targetTable) || !$quickImporter->parentEntity) {
            return;
        }

        $this->framework->getAdapter(Controller::class)->loadDataContainer($importer->targetTable);

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

    private function runImport(bool $dry = false): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $config = $request?->query->get('id');

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

            $query = $this->getCurrentQuery();
            unset($query['key']);

            throw new RedirectResponseException($this->buildUrl(array_merge($query, ['act' => 'edit'])));
        }

        $importer->setDryRun($dry);
        $result = $importer->run();
        $importer->outputFinalResultMessage($result);

        $query = $this->getCurrentQuery();
        unset($query['key'], $query['id']);

        throw new RedirectResponseException($this->buildUrl($query));
    }
}
