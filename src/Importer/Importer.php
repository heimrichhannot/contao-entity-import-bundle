<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Importer;

use Ausi\SlugGenerator\SlugGenerator;
use Contao\Controller;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Framework\ContaoFramework;
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
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Psr\Log\LogLevel;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Style\SymfonyStyle;
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
     * @var Stopwatch
     */
    protected $stopwatch;

    /**
     * @var DatabaseUtil
     */
    private $databaseUtil;

    /**
     * @var DcaUtil
     */
    private $dcaUtil;
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array|null
     */
    private $dbMergeCache;

    /**
     * @var Request
     */
    private $request;
    /**
     * @var FileUtil
     */
    private $fileUtil;
    /**
     * @var Utils
     */
    private $utils;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var ContaoFramework
     */
    private $framework;

    /**
     * Importer constructor.
     */
    public function __construct(
        ContainerInterface $container,
        ContaoFramework $framework,
        Model $configModel,
        SourceInterface $source,
        EventDispatcherInterface $eventDispatcher,
        Request $request,
        DatabaseUtil $databaseUtil,
        DcaUtil $dcaUtil,
        FileUtil $fileUtil,
        Utils $utils
    ) {
        $this->container = $container;
        $this->configModel = $configModel;
        $this->source = $source;
        $this->databaseUtil = $databaseUtil;
        $this->eventDispatcher = $eventDispatcher;
        $this->dcaUtil = $dcaUtil;
        $this->request = $request;
        $this->fileUtil = $fileUtil;
        $this->utils = $utils;
        $this->framework = $framework;
    }

    public function setInputOutput(SymfonyStyle $io): void
    {
        $this->io = $io;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): array
    {
        $this->stopwatch = new Stopwatch();

        if ($this->configModel->processInChunks) {
            $count = 0;
            $duration = 0;

            $chunkSize = 1000;
            $totalCount = $this->source->getTotalItemCount();
            $cycles = $totalCount / $chunkSize;
            $dbIdMapping = new \SplFixedArray($totalCount);
            $dbItemMapping = new \SplFixedArray($totalCount);

            $mappedItems = new \SplFixedArray($totalCount);

            for ($i = 0; $i <= $cycles; ++$i) {
                $this->stopwatch->reset();
                $this->stopwatch->start('contao-entity-import-bundle.id'.$this->configModel->id);

                $options = [
                    'itemLimit' => $chunkSize,
                    'itemOffset' => $i * $chunkSize,
                    'itemTotalCount' => $totalCount,
                ];

                $items = $this->getDataFromSource($options);

                $event = $this->eventDispatcher->dispatch(BeforeImportEvent::NAME, new BeforeImportEvent($items, $this->configModel, $this->source, $this->dryRun, $options));

                $chunkResult = $this->executeImport($event->getItems(), $options);

                // error? -> skip loop and throw error
                if ('error' === $chunkResult['state']) {
                    return $chunkResult;
                }

                $count += $chunkResult['count'];
                $duration += $chunkResult['duration'];

                for ($j = 0; $j < \count($chunkResult['mappedItems']); ++$j) {
                    $mappedItems[$j + $i * $chunkSize] = $chunkResult['mappedItems'][$j];
                }

                for ($j = 0; $j < \count($chunkResult['dbIdMapping']); ++$j) {
                    $dbIdMapping[$j + $i * $chunkSize] = $chunkResult['dbIdMapping'][$j];
                }

                for ($j = 0; $j < \count($chunkResult['dbItemMapping']); ++$j) {
                    $dbItemMapping[$j + $i * $chunkSize] = $chunkResult['dbItemMapping'][$j];
                }

                // last loop?
                if ($i === (int) floor($cycles)) {
                    $this->stopwatch->start('contao-entity-import-bundle.id'.$this->configModel->id);

                    try {
                        $this->postProcess(
                            $this->configModel->targetTable,
                            $mappedItems,
                            $dbIdMapping,
                            $dbItemMapping
                        );
                    } catch (\Exception $e) {
                        $this->stopwatch->stop('contao-entity-import-bundle.id'.$this->configModel->id);

                        $this->sendErrorEmail($e->getMessage());

                        return [
                            'state' => 'error',
                            'error' => $e->getMessage(),
                        ];
                    }

                    $event = $this->stopwatch->stop('contao-entity-import-bundle.id'.$this->configModel->id);

                    $duration += $event->getDuration();
                }

                $this->eventDispatcher->dispatch(AfterImportEvent::NAME, new AfterImportEvent($items, $this->configModel, $this->source, $this->dryRun, $options));
            }

            return [
                'state' => 'success',
                'count' => $count,
                'duration' => $duration,
                'mappedItems' => $mappedItems,
                'dbIdMapping' => $dbIdMapping,
                'dbItemMapping' => $dbItemMapping,
            ];
        }
        $this->stopwatch->start('contao-entity-import-bundle.id'.$this->configModel->id);

        $items = $this->getDataFromSource();

        $event = $this->eventDispatcher->dispatch(BeforeImportEvent::NAME, new BeforeImportEvent($items, $this->configModel, $this->source, $this->dryRun));

        $result = $this->executeImport($event->getItems());

        $this->eventDispatcher->dispatch(AfterImportEvent::NAME, new AfterImportEvent($items, $this->configModel, $this->source, $this->dryRun));

        return $result;
    }

    public function getDataFromSource(array $options = []): array
    {
        return $this->source->getMappedData($options);
    }

    public function getMappedItems(array $options = []): array
    {
        $localizeLabels = $options['localizeLabels'] ?? false;

        $items = $this->getDataFromSource($options);

        $mappedItems = [];

        $mapping = \Contao\StringUtil::deserialize($this->configModel->fieldMapping, true);
        $mapping = $this->adjustMappingForDcMultilingual($mapping);
        $mapping = $this->adjustMappingForChangeLanguage($mapping);

        foreach ($items as $item) {
            $mappedItem = $this->applyFieldMappingToSourceItem($item, $mapping);

            if ($localizeLabels) {
                $localizedItem = [];

                foreach ($mappedItem as $field => $value) {
                    $localizedItem[$this->dcaUtil->getLocalizedFieldName($field, $this->configModel->targetTable)] = $value;
                }

                $mappedItem = $localizedItem;
            }

            $mappedItems[] = $mappedItem;
        }

        return $mappedItems;
    }

    public function setDryRun(bool $dry): void
    {
        $this->dryRun = $dry;
    }

    public function outputResultMessages(array $result): void
    {
        $this->framework->getAdapter(System::class)->loadLanguageFile('tl_entity_import_config');

        if ('error' === $result['state']) {
            $message = $GLOBALS['TL_LANG']['tl_entity_import_config']['error']['errorImport'];

            if ($this->utils->container()->isDev()) {
                if ($this->io) {
                    $message .= "\n\n".$GLOBALS['TL_LANG']['tl_entity_import_config']['error']['error'].':'."\n\n".($result['error'] ?? '');
                } else {
                    $message .= '<br><br>'.$GLOBALS['TL_LANG']['tl_entity_import_config']['error']['error'].':'.'<br><br>'.($result['error'] ?? '');
                }
            }

            if ($this->io) {
                $this->io->error($message);
            } else {
                Message::addError($message);
            }
        } else {
            $count = $result['count'];
            $duration = $result['duration'];

            $duration = str_replace('.', ',', round($duration / 1000, 2));

            if ($count > 0) {
                if ($this->io) {
                    $this->io->success(sprintf(
                        $GLOBALS['TL_LANG']['tl_entity_import_config']['error']['successfulImport'], $count, $duration,
                        System::getReadableSize(memory_get_peak_usage())
                    ));
                } else {
                    Message::addConfirmation(sprintf(
                        $GLOBALS['TL_LANG']['tl_entity_import_config']['error']['successfulImport'], $count, $duration,
                        System::getReadableSize(memory_get_peak_usage())
                    ));
                }
            } else {
                if ($this->io) {
                    $this->io->warning(sprintf($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['emptyFile']));
                } else {
                    Message::addInfo(sprintf($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['emptyFile']));
                }
            }
        }
    }

    public function sendErrorEmail(string $errorMessage)
    {
        $config = $this->getDebugConfig();

        if ($this->configModel->errorNotificationLock || $this->dryRun) {
            return;
        }

        if (isset($config['contao_log']) && $config['contao_log']) {
            $this->utils->container()->log($errorMessage, 'Importer::executeImport', LogLevel::ERROR);
        }

        if (isset($config['email']) && $config['email']) {
            $email = new Email();
            $email->subject = sprintf($GLOBALS['TL_LANG']['MSC']['entityImport']['exceptionEmailSubject'], $this->configModel->title);
            $email->text = sprintf('An error occurred on domain "%s"', $this->configModel->cronDomain).' : '.$errorMessage;
            $email->sendTo($this->configModel->errorNotificationEmail ?: $GLOBALS['TL_CONFIG']['adminEmail']);
        }

        $this->databaseUtil->update('tl_entity_import_config', ['errorNotificationLock' => '1'], 'tl_entity_import_config.id=?', [$this->configModel->id]);
    }

    public function postProcess(string $table, $mappedItems, $dbIdMapping, $dbItemMapping)
    {
        // DC_Multilingual -> fix langPid (can only be done after all items are imported due to order issues otherwise)
        if (class_exists('\Terminal42\DcMultilingualBundle\Terminal42DcMultilingualBundle') &&
            $this->configModel->addDcMultilingualSupport) {
            $langPidField = $GLOBALS['TL_DCA'][$table]['config']['langPid'] ?? 'langPid';

            foreach ($dbItemMapping as $itemMapping) {
                if (!$itemMapping['source']['langPid']) {
                    continue;
                }

                if (!$this->dryRun) {
                    $this->databaseUtil->update($table, [
                        $table.'.'.$langPidField => $dbIdMapping[$itemMapping['source']['langPid']],
                    ], "$table.id=?", [$itemMapping['target']->id]);
                }
            }
        }

        // Drafts -> fix draftParent (can only be done after all items are imported due to order issues otherwise)
        if (class_exists('\HeimrichHannot\DraftsBundle\ContaoDraftsBundle') &&
            $this->configModel->addDraftsSupport) {
            foreach ($dbItemMapping as $itemMapping) {
                if (!$itemMapping['source']['draftParent']) {
                    continue;
                }

                if (!$this->dryRun) {
                    $this->databaseUtil->update($table, [
                        $table.'.draftParent' => $dbIdMapping[$itemMapping['source']['draftParent']],
                    ], "$table.id=?", [$itemMapping['target']->id]);
                }
            }
        }

        // change language -> fix languageMain (can only be done after all items are imported due to order issues otherwise)
        if (class_exists('\Terminal42\ChangeLanguage\Language') && $this->configModel->addChangeLanguageSupport) {
            foreach ($dbItemMapping as $itemMapping) {
                if (!$itemMapping['source']['languageMain']) {
                    continue;
                }

                // map the languageMain id
                $newsroomPost = $this->databaseUtil->findOneResultBy($table, [
                    $table.'.'.$this->configModel->changeLanguageTargetExternalIdField.'=?',
                ], [
                    $itemMapping['source']['languageMain'],
                ]);

                if (!$this->dryRun && $newsroomPost->numRows > 0) {
                    $this->databaseUtil->update($table, [
                        $table.'.languageMain' => $newsroomPost->id,
                    ], "$table.id=?", [$itemMapping['target']->id]);
                }
            }
        }

        $this->deleteAfterImport($mappedItems);
        $this->applySorting();

        $this->databaseUtil->commitTransaction();
    }

    protected function applyFieldMappingToSourceItem(array $item, array $mapping): ?array
    {
        $mapped = [];

        foreach ($mapping as $mappingElement) {
            if (isset($mappingElement['skip']) && $mappingElement['skip']) {
                continue;
            }

            if ('source_value' === $mappingElement['valueType']) {
                $mapped[$mappingElement['columnName']] = trim($item[$mappingElement['mappingValue']]);
            } elseif ('static_value' === $mappingElement['valueType']) {
                $mapped[$mappingElement['columnName']] = trim(
                    $this->framework->getAdapter(Controller::class)->replaceInsertTags($mappingElement['staticValue'])
                );
            }
        }

        return $mapped;
    }

    protected function executeImport(array $items, array $options = []): array
    {
        $this->dcaUtil->loadLanguageFile('default');
        $this->dcaUtil->loadLanguageFile('tl_entity_import_config');

        $database = Database::getInstance();
        $table = $this->configModel->targetTable;

        if (!$database->tableExists($table)) {
            throw new Exception($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['tableDoesNotExist']);
        }

        try {
            $count = 0;
            $targetTableColumns = $database->getFieldNames($table);
            $targetTableColumnData = [];

            foreach ($database->listFields($table) as $columnData) {
                $targetTableColumnData[$columnData['name']] = $columnData;
            }

            $mappedItems = new \SplFixedArray(\count($items));

            $mode = $this->configModel->importMode;

            $mapping = \Contao\StringUtil::deserialize($this->configModel->fieldMapping, true);
            $mapping = $this->adjustMappingForDcMultilingual($mapping);
            $mapping = $this->adjustMappingForChangeLanguage($mapping);

            $dbIdMapping = [];
            $dbItemMapping = [];

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

            foreach ($items as $i => $item) {
                $mappedItem = $this->applyFieldMappingToSourceItem($item, $mapping);

                $mappedItem = $this->fixNotNullErrors($mappedItem, $targetTableColumnData);

                if (!\is_array($mappedItem)) {
                    throw new Exception($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['configFieldMapping']);
                }

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
                        $set = array_merge($set, $this->applyFieldFileMapping($record, $mappedItem));

                        if (!empty($set) && !$this->dryRun) {
                            $this->databaseUtil->update($table, $set, "$table.id=?", [$record->id]);
                        }

                        $importedRecord = $record;
                    }
                } elseif ('merge' === $mode) {
                    $key = implode('||', array_map(function ($field) use ($item) {
                        return $item[$field];
                    }, $identifierFields));

                    if ($key && isset($this->dbMergeCache[$key]) &&
                        ($existingRecord = $this->databaseUtil->findResultByPk($table, $this->dbMergeCache[$key])) && $existingRecord->numRows > 0) {
                        $this->updateMappingItemForSkippedFields($mappedItem);

                        $existing = (object) $existingRecord->row();

                        $set = $this->setDateAdded($existing);
                        $set = array_merge($set, $this->generateAlias($existing));
                        $set = array_merge($set, $this->setTstamp($existing));
                        $set = array_merge($set, $this->applyFieldFileMapping($existing, $mappedItem));

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
                            $set = array_merge($set, $this->applyFieldFileMapping($record, $mappedItem));

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
                    $dbIdMapping[$item['__id']] = $importedRecord->id;
                }

                $dbItemMapping[] = [
                    'source' => [
                        'langPid' => $item['langPid'],
                        'draftParent' => $item['draftParent'],
                        'languageMain' => $item['languageMain'],
                    ],
                    'target' => [
                        'id' => $importedRecord->id,
                    ],
                ];

                // categories bundle
                $this->importCategoryAssociations($mapping, $item, $importedRecord->id);

                /* @var AfterItemImportEvent $event */
                $this->eventDispatcher->dispatch(AfterItemImportEvent::NAME, new AfterItemImportEvent(
                    $importedRecord,
                    $mappedItem,
                    $item,
                    $mapping,
                    $this->configModel,
                    $this->source,
                    $this->dryRun
                ));

                $mappedItems[$i] = $event->getMappedItem();
            }

            // if processing in chunks is activated, postProcess() needs to be done after all chunks have been done
            if (!$this->configModel->processInChunks) {
                $this->postProcess($table, $mappedItems, $dbIdMapping, $dbItemMapping);
            }
        } catch (\Exception $e) {
            $this->stopwatch->stop('contao-entity-import-bundle.id'.$this->configModel->id);

            $this->sendErrorEmail($e->getMessage());

            return [
                'state' => 'error',
                'error' => $e->getMessage(),
            ];
        }

        if ($this->request->getGet('redirect_url')) {
            throw new RedirectResponseException(html_entity_decode($this->request->getGet('redirect_url')));
        }

        $event = $this->stopwatch->stop('contao-entity-import-bundle.id'.$this->configModel->id);

        $duration = $event->getDuration();

        if ($this->configModel->errorNotificationLock) {
            $this->databaseUtil->update('tl_entity_import_config', ['errorNotificationLock' => ''], 'tl_entity_import_config.id=?', [$this->configModel->id]);
        }

        return [
            'state' => 'success',
            'count' => $count,
            'duration' => $duration,
            'mappedItems' => $mappedItems,
            'dbIdMapping' => $dbIdMapping,
            'dbItemMapping' => $dbItemMapping,
        ];
    }

    protected function fixNotNullErrors($mappedItem, $targetTableColumnData)
    {
        $result = [];

        foreach ($mappedItem as $field => $value) {
            if (null === $value && isset($targetTableColumnData[$field]['null']) &&
                'NOT NULL' === $targetTableColumnData[$field]['null'] && isset($targetTableColumnData[$field]['default'])) {
                $result[$field] = $targetTableColumnData[$field]['default'];
            } else {
                $result[$field] = $value;
            }
        }

        return $result;
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

        $categoryManager = System::getContainer()->get('huh.categories.manager');

        $table = $this->configModel->targetTable;
        $sourceTable = $this->source->getSourceModel()->dbSourceTable;

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
                $categories = $categoryManager->findAssociationsByParentTableAndEntityAndField(
                    $sourceTable, $item['externalId'], $mappingElement['mappingValue']
                );

                if (null !== $categories) {
                    $categories = $categories->fetchEach('category');

                    // insert the associations if not already existing
                    $existing = $categoryManager->findByEntityAndCategoryFieldAndTable(
                        $targetId, $targetField, $table
                    );

                    if (null === $existing) {
                        if (!$this->dryRun) {
                            $categoryManager->createAssociations(
                                $targetId, $targetField, $table, $categories
                            );
                        }
                    } else {
                        $existingIds = $existing->fetchEach('id');

                        $idsToInsert = array_diff($categories, $existingIds);

                        if (!empty($idsToInsert)) {
                            if (!$this->dryRun) {
                                $categoryManager->createAssociations(
                                    $targetId, $targetField, $table, $idsToInsert
                                );
                            }
                        }
                    }
                } else {
                    if (!$this->dryRun) {
                        // remove associations potentially added before
                        $categoryManager->removeAllAssociations($targetId, $targetField, $table);
                    }
                }
            }
        }
    }

    protected function initDbCacheForMerge(array $mergeIdentifiers)
    {
        if (\is_array($this->dbMergeCache)) {
            return;
        }

        $this->dbMergeCache = [];

        $table = $this->configModel->targetTable;

        if (null === ($records = $this->databaseUtil->findResultsBy($table, ['pid=?'], [2])) || $records->numRows < 1) {
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

            $cache[$key] = $records->id;
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
            $this->databaseUtil->delete($table, html_entity_decode($this->configModel->deleteBeforeImportWhere));
        }
    }

    protected function deleteAfterImport($mappedSourceItems)
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
                    $identifiers = '';

                    foreach ($mappedSourceItems as $i => $value) {
                        $identifiers .= '"'.$value[$deletionIdentifier['source']].'",';
                    }

                    $identifiers = rtrim($identifiers, ',');

                    if ($identifiers) {
                        $conditions[] = '('.$table.'.'.$deletionIdentifier['target'].' NOT IN ('.$identifiers.'))';
                    }
                }

                if ($this->configModel->targetDeletionAdditionalWhere) {
                    $conditions[] = '('.html_entity_decode($this->configModel->targetDeletionAdditionalWhere).')';
                }

                if (!$this->dryRun && !empty($conditions)) {
                    $this->databaseUtil->delete($table, implode(' AND ', $conditions), []);
                }

                break;

            case EntityImportConfigContainer::DELETION_MODE_TARGET_FIELDS:
                if ($this->configModel->deleteBeforeImport && !$this->dryRun) {
                    $this->databaseUtil->delete($table, html_entity_decode($this->configModel->targetDeletionWhere));
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
            if ($record->{$mapping['targetField']} && $mapping['skipIfExisting']) {
                continue;
            }

            // retrieve the file
            $content = $this->fileUtil->retrieveFileContent(
                $item[$mapping['mappingField']], $this->utils->container()->isBackend()
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

        $where = $this->framework->getAdapter(Controller::class)->replaceInsertTags(
            html_entity_decode($this->configModel->targetSortingContextWhere), false
        );

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

    protected function adjustMappingForChangeLanguage(array $mapping)
    {
        if (!class_exists('\Terminal42\ChangeLanguage\Language') || !$this->configModel->addChangeLanguageSupport) {
            return $mapping;
        }

        $mapping[] = [
            'columnName' => 'languageMain',
            'valueType' => 'source_value',
            'mappingValue' => 'languageMain',
        ];

        return $mapping;
    }
}
