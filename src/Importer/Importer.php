<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Importer;

use Ausi\SlugGenerator\SlugGenerator;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\Database;
use Contao\Email;
use Contao\File;
use Contao\Folder;
use Contao\Message;
use Contao\Model;
use Contao\System;
use Contao\Validator;
use HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer;
use HeimrichHannot\EntityImportBundle\Event\AfterImportEvent;
use HeimrichHannot\EntityImportBundle\Event\AfterItemImportEvent;
use HeimrichHannot\EntityImportBundle\Event\BeforeFileImportEvent;
use HeimrichHannot\EntityImportBundle\Event\BeforeImportEvent;
use HeimrichHannot\EntityImportBundle\Event\BeforeItemImportEvent;
use HeimrichHannot\EntityImportBundle\Model\EntityImportConfigModel;
use HeimrichHannot\EntityImportBundle\Source\SourceInterface;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\String\StringUtil;
use Psr\Log\LogLevel;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class Importer implements ImporterInterface
{
    /**
     * @var SourceInterface
     */
    protected $source;

    /**
     * @var EntityImportConfigModel
     */
    protected $configModel;

    /**
     * @var bool
     */
    protected $dryRun = false;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var DatabaseUtil
     */
    private $databaseUtil;

    /**
     * @var ModelUtil
     */
    private $modelUtil;
    /**
     * @var StringUtil
     */
    private $stringUtil;
    /**
     * @var DcaUtil
     */
    private $dcaUtil;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var ContainerUtil
     */
    private $containerUtil;

    /**
     * @var array|null
     */
    private $dbMergeCache;

    /**
     * @var array|null
     */
    private $dbIdMapping;

    /**
     * @var array|null
     */
    private $dbItemMapping;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var FileUtil
     */
    private $fileUtil;

    /**
     * Importer constructor.
     */
    public function __construct(
        ContainerInterface $container,
        Model $configModel,
        SourceInterface $source,
        EventDispatcherInterface $eventDispatcher,
        Request $request,
        DatabaseUtil $databaseUtil,
        ModelUtil $modelUtil,
        StringUtil $stringUtil,
        DcaUtil $dcaUtil,
        ContainerUtil $containerUtil,
        FileUtil $fileUtil
    ) {
        $this->container = $container;
        $this->configModel = $configModel;
        $this->source = $source;
        $this->databaseUtil = $databaseUtil;
        $this->eventDispatcher = $eventDispatcher;
        $this->modelUtil = $modelUtil;
        $this->stringUtil = $stringUtil;
        $this->dcaUtil = $dcaUtil;
        $this->containerUtil = $containerUtil;
        $this->request = $request;
        $this->fileUtil = $fileUtil;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): bool
    {
        $items = $this->getDataFromSource();

        $event = $this->eventDispatcher->dispatch(BeforeImportEvent::NAME, new BeforeImportEvent($items, $this->configModel, $this->source, $this->dryRun));

        $result = $this->executeImport($event->getItems());

        $this->eventDispatcher->dispatch(AfterImportEvent::NAME, new AfterImportEvent($items, $this->configModel, $this->source, $this->dryRun));

        return $result;
    }

    public function getDataFromSource(): array
    {
        return $this->source->getMappedData();
    }

    public function setDryRun(bool $dry)
    {
        $this->dryRun = $dry;
    }

    protected function applyFieldMappingToSourceItem(array $item, array $mapping): ?array
    {
        $mapped = [];

        foreach ($mapping as $mappingElement) {
            if (isset($mappingElement['skip']) && $mappingElement['skip']) {
                continue;
            }

            if ('source_value' === $mappingElement['valueType']) {
                $mapped[$mappingElement['columnName']] = $item[$mappingElement['mappingValue']];
            } elseif ('static_value' === $mappingElement['valueType']) {
                $mapped[$mappingElement['columnName']] = $this->stringUtil->replaceInsertTags($mappingElement['staticValue']);
            }
        }

        return $mapped;
    }

    protected function executeImport(array $items): bool
    {
        $stopwatch = new Stopwatch();

        $stopwatch->start('contao-entity-import-bundle.id'.$this->configModel->id);

        $this->dcaUtil->loadLanguageFile('default');
        $database = Database::getInstance();
        $table = $this->configModel->targetTable;

        if (!$database->tableExists($table)) {
            new Exception($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['tableDoesNotExist']);
        }

        try {
            $count = 0;
            $targetTableColumns = $database->getFieldNames($table);
            $mappedItems = [];

            $mode = $this->configModel->importMode;

            $mapping = \Contao\StringUtil::deserialize($this->configModel->fieldMapping, true);
            $mapping = $this->adjustMappingForDcMultilingual($mapping);

            $this->dbIdMapping = [];
            $this->dbItemMapping = [];

            if ('merge' === $mode) {
                $mergeIdentifiers = \Contao\StringUtil::deserialize($this->configModel->mergeIdentifierFields, true);

                if (empty(array_filter($mergeIdentifiers))) {
                    throw new Exception($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['noIdentifierFields']);
                }

                $this->initDbCacheForMerge($mergeIdentifiers);

                $identifierFields = [];

                foreach ($mergeIdentifiers as $mergeIdentifier) {
                    $identifierFields[] = $mergeIdentifier['source'];
                }
            }

            $this->deleteBeforeImport();

            $this->databaseUtil->beginTransaction();

            foreach ($items as $item) {
                $mappedItem = $this->applyFieldMappingToSourceItem($item, $mapping);

                if (!\is_array($mappedItem)) {
                    throw new Exception($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['configFieldMapping']);
                }

                $mappedItems[] = $mappedItem;

                $columnsNotExisting = array_diff(array_keys($mappedItem), $targetTableColumns);

                if (!empty($columnsNotExisting)) {
                    throw new Exception($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['tableFieldsDiffer']);
                }

                /** @var BeforeItemImportEvent $event */
                $event = $this->eventDispatcher->dispatch(BeforeItemImportEvent::NAME, new BeforeItemImportEvent(
                    $mappedItem,
                    $item,
                    $this->configModel,
                    $this->source,
                    false,
                    $this->dryRun
                ));

                $item = $event->getItem();
                $mappedItem = $event->getMappedItem();

                // developer can decide to skip the item in an event listener if certain criteria is met
                if ($event->isSkipped()) {
                    continue;
                }

                ++$count;
                $importedRecord = null;

                if ('insert' === $mode) {
                    if (!$this->dryRun) {
                        $statement = $this->databaseUtil->insert($table, $mappedItem);

                        $record = (object) $mappedItem;
                        $record->id = $statement->insertId;

                        $set = $this->setDateAdded($record);
                        $set = array_merge($set, $this->generateAlias($record));
                        $set = array_merge($set, $this->setTstamp($record));
                        $set = array_merge($set, $this->applyFieldFileMapping($record, $item));

                        if (!empty($set) && !$this->dryRun) {
                            $this->databaseUtil->update($table, $set, "$table.id=?", [$record->id]);
                        }

                        $importedRecord = $record;
                    }
                } elseif ('merge' === $mode) {
                    $key = implode('||', array_map(function ($field) use ($item) {
                        return $item[$field];
                    }, $identifierFields));

                    if ($key && isset($this->dbMergeCache[$key])) {
                        $this->updateMappingItemForSkippedFields($mappedItem);

                        $existing = (object) $this->dbMergeCache[$key];

                        $set = $this->setDateAdded($existing);
                        $set = array_merge($set, $this->generateAlias($existing));
                        $set = array_merge($set, $this->setTstamp($existing));
                        $set = array_merge($set, $this->applyFieldFileMapping($existing, $item));

                        if (!$this->dryRun) {
                            $this->databaseUtil->update($table, array_merge($mappedItem, $set), "$table.id=?", [$existing->id]);
                        }

                        $importedRecord = $existing;
                    } else {
                        if (!$this->dryRun) {
                            $statement = $this->databaseUtil->insert($table, $mappedItem);

                            $record = (object) $mappedItem;
                            $record->id = $statement->insertId;

                            $set = $this->setDateAdded($record);
                            $set = array_merge($set, $this->generateAlias($record));
                            $set = array_merge($set, $this->setTstamp($record));
                            $set = array_merge($set, $this->applyFieldFileMapping($record, $item));

                            if (!empty($set) && !$this->dryRun) {
                                $this->databaseUtil->update($table, $set, "$table.id=?", [$record->id]);
                            }

                            $importedRecord = $record;
                        }
                    }
                } else {
                    throw new Exception($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['modeNotSet']);
                }

                // store mapping e.g. for DC_Multilingual -> only possible if an id field exists
                if (isset($item['__id'])) {
                    $this->dbIdMapping[$item['__id']] = $importedRecord->id;
                }

                $this->dbItemMapping[] = [
                    'source' => $item,
                    'target' => $importedRecord,
                ];

                // categories bundle
                $this->importCategoryAssociations($mapping, $item, $importedRecord->id);

                /* @var AfterItemImportEvent $event */
                $this->eventDispatcher->dispatch(AfterItemImportEvent::NAME, new AfterItemImportEvent(
                    $importedRecord,
                    $mappedItem,
                    $item,
                    $this->configModel,
                    $this->source,
                    $this->dryRun
                ));

                $mappedItems[] = $event->getMappedItem();
            }

            // DC_Multilingual -> fix langPid (can only be done after all items are imported due to order issues otherwise)
            if (class_exists('\Terminal42\DcMultilingualBundle\Terminal42DcMultilingualBundle') &&
                $this->configModel->addDcMultilingualSupport) {
                $langPidField = $GLOBALS['TL_DCA'][$table]['config']['langPid'] ?? 'langPid';

                foreach ($this->dbItemMapping as $itemMapping) {
                    if (!$itemMapping['source']['langPid']) {
                        continue;
                    }

                    if (!$this->dryRun) {
                        $this->databaseUtil->update($table, [
                            $table.'.'.$langPidField => $this->dbIdMapping[$itemMapping['source']['langPid']],
                        ], "$table.id=?", [$itemMapping['target']->id]);
                    }
                }
            }

            $this->deleteAfterImport($mappedItems);
            $this->applySorting();

            $this->databaseUtil->commitTransaction();

            $event = $stopwatch->stop('contao-entity-import-bundle.id'.$this->configModel->id);

            if ($count > 0) {
                $duration = str_replace('.', ',', round($event->getDuration() / 1000, 2));

                Message::addConfirmation(sprintf($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['successfulImport'], $count, $duration));
            } else {
                Message::addInfo(sprintf($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['emptyFile']));
            }

            if ($this->configModel->errorNotificationLock) {
                $this->databaseUtil->update('tl_entity_import_config', ['errorNotificationLock' => ''], 'tl_entity_import_config.id=?', [$this->configModel->id]);
            }
        } catch (\Exception $e) {
            $config = $this->getDebugConfig();

            if (!$this->configModel->errorNotificationLock && !$this->dryRun) {
                if (isset($config['contao_log']) && $config['contao_log']) {
                    $this->containerUtil->log($e, 'executeImport', LogLevel::ERROR);
                }

                if (isset($config['email']) && $config['email']) {
                    $email = new Email();
                    $email->subject = sprintf($GLOBALS['TL_LANG']['MSC']['entityImport']['exceptionEmailSubject'], $this->configModel->title);
                    $email->text = sprintf('An error occurred on domain "%s"', $this->configModel->cronDomain).' : '.$e->getMessage();
                    $email->sendTo($GLOBALS['TL_CONFIG']['adminEmail']);
                }

                $this->databaseUtil->update('tl_entity_import_config', ['errorNotificationLock' => '1'], 'tl_entity_import_config.id=?', [$this->configModel->id]);
            }

            Message::addError(sprintf($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['errorImport'], $count,
                $this->containerUtil->isDev() ? str_replace("\n", '<br>', $e) : $e->getMessage()));

            return false;
        }

        if ($this->request->getGet('redirect_url')) {
            throw new RedirectResponseException(html_entity_decode($this->request->getGet('redirect_url')));
        }

        return true;
    }

    /**
     * @param $mappingItem
     */
    protected function updateMappingItemForSkippedFields(array &$mappingItem): void
    {
        if (!$this->configModel->addSkipFieldsOnMerge) {
            return;
        }

        $skipFields = \Contao\StringUtil::deserialize($this->configModel->skipFieldsOnMerge, true);

        foreach ($skipFields as $skipField) {
            if (!\array_key_exists($skipField, $mappingItem)) {
                continue;
            }

            unset($mappingItem[$skipField]);
        }
    }

    protected function importCategoryAssociations(array $mapping, array $item, $targetId)
    {
        if (!$this->configModel->addCategoriesSupport || !$targetId) {
            return;
        }

        $table = $this->configModel->targetTable;

        $dca = &$GLOBALS['TL_DCA'][$table];

        foreach ($mapping as $mappingElement) {
            if (isset($mappingElement['skip']) && $mappingElement['skip']) {
                continue;
            }

            if ('source_value' !== $mappingElement['valueType']) {
                continue;
            }

            $targetField = $mappingElement['columnName'];

            if ((isset($dca['fields'][$targetField]['eval']['isCategoryField']) && $dca['fields'][$targetField]['eval']['isCategoryField'] ||
                isset($dca['fields'][$targetField]['eval']['isCategoriesField']) && $dca['fields'][$targetField]['eval']['isCategoriesField'])
            ) {
                $categories = \Contao\StringUtil::deserialize($item[$mappingElement['mappingValue']], true);

                if (!empty($categories)) {
                    // insert the associations if not already existing
                    $existing = System::getContainer()->get('huh.categories.manager')->findByEntityAndCategoryFieldAndTable(
                        $targetId, $targetField, $table
                    );

                    if (null === $existing) {
                        if (!$this->dryRun) {
                            System::getContainer()->get('huh.categories.manager')->createAssociations(
                                $targetId, $targetField, $table, $categories
                            );
                        }
                    } else {
                        $existingIds = $existing->fetchEach('id');

                        $idsToInsert = array_diff($categories, $existingIds);

                        if (!empty($idsToInsert)) {
                            if (!$this->dryRun) {
                                System::getContainer()->get('huh.categories.manager')->createAssociations(
                                    $targetId, $targetField, $table, $idsToInsert
                                );
                            }
                        }
                    }
                }
            }
        }
    }

    protected function initDbCacheForMerge(array $mergeIdentifiers)
    {
        $this->dbMergeCache = [];

        $table = $this->configModel->targetTable;

        if (null === ($records = $this->databaseUtil->findResultsBy($table, null, null)) || $records->numRows < 1) {
            $this->dbMergeCache = [];

            return;
        }

        $identifierFields = [];

        foreach ($mergeIdentifiers as $mergeIdentifier) {
            $identifierFields[] = $mergeIdentifier['target'];
        }

        $cache = [];

        while ($records->next()) {
            $key = implode('||', array_map(function ($field) use ($records) {
                return $records->{$field};
            }, $identifierFields));

            if (!$key) {
                continue;
            }

            $cache[$key] = $records->row();
        }

        $this->dbMergeCache = $cache;
    }

    protected function getDebugConfig(): ?array
    {
        $config = $this->container->getParameter('huh_entity_import');

        return $config['debug'];
    }

    protected function deleteBeforeImport()
    {
        $table = $this->configModel->targetTable;

        if ($this->configModel->deleteBeforeImport && !$this->dryRun) {
            $this->databaseUtil->delete($table, $this->configModel->deleteBeforeImportWhere);
        }
    }

    protected function deleteAfterImport(array $mappedSourceItems)
    {
        $table = $this->configModel->targetTable;

        switch ($this->configModel->deletionMode) {
            case EntityImportConfigContainer::DELETION_MODE_MIRROR:
                $deletionIdentifiers = \Contao\StringUtil::deserialize($this->configModel->deletionIdentifierFields, true);

                if (empty($deletionIdentifiers)) {
                    throw new Exception($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['noIdentifierFields']);
                }

                $conditions = [];

                foreach ($deletionIdentifiers as $deletionIdentifier) {
                    $sourceValues = array_map(function ($item) use ($deletionIdentifier) {
                        return '"'.$item[$deletionIdentifier['source']].'"';
                    }, $mappedSourceItems);

                    $conditions[] = '('.$table.'.'.$deletionIdentifier['target'].' NOT IN ('.implode(',', $sourceValues).'))';
                }

                if ($this->configModel->targetDeletionAdditionalWhere) {
                    $conditions[] = '('.$this->configModel->targetDeletionAdditionalWhere.')';
                }

                if (!$this->dryRun && !empty($conditions)) {
                    $this->databaseUtil->delete($table, implode(' AND ', $conditions), []);
                }

                break;

            case EntityImportConfigContainer::DELETION_MODE_TARGET_FIELDS:
                if ($this->configModel->deleteBeforeImport && !$this->dryRun) {
                    $this->databaseUtil->delete($table, $this->configModel->targetDeletionWhere);
                }

                break;
        }
    }

    protected function setDateAdded($record): array
    {
        $table = $this->configModel->targetTable;
        $field = $this->configModel->targetDateAddedField;

        if (!$this->configModel->setDateAdded || !$field || $record->{$field} || !$record->id) {
            return [];
        }

        $time = time();

        $record->{$field} = $time;

        return [
            $field => $time,
        ];
    }

    protected function setTstamp($record): array
    {
        $field = $this->configModel->targetTstampField;

        if (!$this->configModel->setTstamp || !$field || !$record->id) {
            return [];
        }

        $time = time();

        $record->{$field} = $time;

        return [
            $field => time(),
        ];
    }

    protected function generateAlias($record): array
    {
        $table = $this->configModel->targetTable;
        $field = $this->configModel->targetAliasField;
        $fieldPattern = $this->configModel->aliasFieldPattern;

        if (!$this->configModel->generateAlias || !$field || !$fieldPattern || !$record->id) {
            return [];
        }

        $aliasBase = preg_replace_callback(
            '@%([^%]+)%@i',
            function ($matches) use ($record) {
                return $record->{$matches[1]};
            },
            $fieldPattern
        );

        $alias = $this->dcaUtil->generateAlias(
            $record->{$field},
            $record->id,
            $table,
            $aliasBase
        );

        $record->{$field} = $alias;

        return [
            $field => $alias,
        ];
    }

    protected function applyFieldFileMapping($record, $item): array
    {
        $set = [];
        $slugGenerator = new SlugGenerator();
        $fileMapping = \Contao\StringUtil::deserialize($this->configModel->fileFieldMapping, true);

        foreach ($fileMapping as $mapping) {
            // retrieve the file
            $content = $this->fileUtil->retrieveFileContent(
                $item[$mapping['mappingField']], $this->containerUtil->isBackend()
            );

            // sleep after http requests because of a possible rate limiting
            if (Validator::isUrl($item[$mapping['mappingField']]) && $mapping['delayAfter'] > 0) {
                sleep((int) ($mapping['delayAfter']));
            }

            // no file found?
            if (!$content) {
                $set[$mapping['targetField']] = null;

                continue;
            }

            // generate the file name
            switch ($mapping['namingMode']) {
                case 'random_md5':
                    $filename = md5(rand(0, 99999999999999));

                    break;

                case 'field_pattern':
                    $filename = preg_replace_callback(
                        '@%([^%]+)%@i',
                        function ($matches) use ($record) {
                            return $record->{$matches[1]};
                        },
                        $mapping['filenamePattern']
                    );

                    break;
            }

            if ($mapping['slugFilename']) {
                $filename = $slugGenerator->generate($filename);
            }

            $extension = $this->fileUtil->getExtensionFromFileContent($content);

            $extension = $extension ? '.'.$extension : '';

            // check if a file of that name already exists
            $folder = new Folder($this->fileUtil->getPathFromUuid($mapping['targetFolder']));

            $filenameWithoutExtension = $folder->path.'/'.$filename;

            $file = new File($filenameWithoutExtension.$extension);

            if ($file->exists()) {
                if (!$record->{$mapping['targetField']}) {
                    // no reference -> create the file with an incremented suffix
                    $i = 1;

                    while ($file->exists()) {
                        $filenameWithoutExtension .= '-'.$i++;
                        $file = new File($filenameWithoutExtension.$extension);
                    }
                } else {
                    // only rewrite if content has changed
                    if ($file->getContent() === $content) {
                        continue;
                    }
                }
            }

            $event = $this->eventDispatcher->dispatch(BeforeFileImportEvent::NAME, new BeforeFileImportEvent(
                $file->path,
                $content,
                (array) $record,
                $item,
                $this->configModel,
                $this->source,
                $this->dryRun
            ));

            $file = new File($event->getPath());

            if (!$this->dryRun) {
                $file->write($event->getContent());
                $file->close();

                $set[$mapping['targetField']] = $file->getModel()->uuid;
            }
        }

        return $set;
    }

    protected function applySorting()
    {
        $field = $this->configModel->targetSortingField;
        $where = $this->stringUtil->replaceInsertTags($this->configModel->targetSortingContextWhere, false);
        $order = $this->configModel->targetSortingOrder;

        if (!$this->configModel->sortingMode || !$field || !$where || !$order) {
            return;
        }

        $table = $this->configModel->targetTable;

        switch ($this->configModel->sortingMode) {
            case EntityImportConfigContainer::SORTING_MODE_TARGET_FIELDS:
                $results = $this->databaseUtil->findResultsBy($table, [$where], [], [
                    'order' => $order,
                ]);

                if (null === $where || $results->numRows < 1) {
                    return;
                }

                $count = 1;

                while ($results->next()) {
                    if ($this->dryRun) {
                        continue;
                    }

                    $this->databaseUtil->update($table, [
                        $field => $count++ * 128,
                    ], "$table.id=?", [$results->id]);
                }

                break;
        }
    }

    protected function adjustMappingForDcMultilingual(array $mapping)
    {
        // DC_Multilingual
        if (!class_exists('\Terminal42\DcMultilingualBundle\Terminal42DcMultilingualBundle') || !$this->configModel->addDcMultilingualSupport) {
            return $mapping;
        }

        $table = $this->configModel->targetTable;

        $this->dcaUtil->loadDc($table);

        $dca = $GLOBALS['TL_DCA'][$table];

        $langPidField = $dca['config']['langPid'] ?? 'langPid';
        $languageField = $dca['config']['langColumnName'] ?? 'language';

        $mapping[] = [
            'columnName' => $langPidField,
            'valueType' => 'source_value',
            'mappingValue' => 'langPid',
        ];

        $mapping[] = [
            'columnName' => $languageField,
            'valueType' => 'source_value',
            'mappingValue' => 'language',
        ];

        if (class_exists('HeimrichHannot\DcMultilingualUtilsBundle\ContaoDcMultilingualUtilsBundle') && isset($dca['config']['langPublished'])) {
            $publishedField = $dca['config']['langPublished'] ?? 'langPublished';

            $mapping[] = [
                'columnName' => $publishedField,
                'valueType' => 'source_value',
                'mappingValue' => 'langPublished',
            ];

            if ($dca['config']['langStart']) {
                $publishedStartField = $dca['config']['langStart'] ?? 'langStart';
                $publishedStopField = $dca['config']['langStop'] ?? 'langStop';

                $mapping[] = [
                    'columnName' => $publishedStartField,
                    'valueType' => 'source_value',
                    'mappingValue' => 'langStart',
                ];

                $mapping[] = [
                    'columnName' => $publishedStopField,
                    'valueType' => 'source_value',
                    'mappingValue' => 'langStop',
                ];
            }
        }

        return $mapping;
    }
}
