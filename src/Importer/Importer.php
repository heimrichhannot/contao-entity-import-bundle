<?php

namespace HeimrichHannot\EntityImportBundle\Importer;

use Ausi\SlugGenerator\SlugGenerator;
use Contao\CoreBundle\Slug\Slug;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\Controller;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\Email;
use Contao\File;
use Contao\Folder;
use Contao\Message;
use Contao\System;
use Contao\Validator;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Doctrine\DBAL\Connection;
use HeimrichHannot\EntityImportBundle\EventListener\DataContainer\EntityImportConfigContainer;
use HeimrichHannot\EntityImportBundle\Event\AfterImportEvent;
use HeimrichHannot\EntityImportBundle\Event\AfterItemImportEvent;
use HeimrichHannot\EntityImportBundle\Event\BeforeFileImportEvent;
use HeimrichHannot\EntityImportBundle\Event\BeforeImportEvent;
use HeimrichHannot\EntityImportBundle\Event\BeforeItemImportEvent;
use HeimrichHannot\EntityImportBundle\Model\EntityImportConfigModel;
use HeimrichHannot\EntityImportBundle\Source\SourceInterface;
use HeimrichHannot\EntityImportBundle\Util\EntityImportUtil;
use Psr\Log\LogLevel;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Importer implements ImporterInterface
{
    protected bool $dryRun = false;
    protected bool $webCronMode = false;
    protected Stopwatch $stopwatch;
    protected $dbMergeCache;
    protected ?SymfonyStyle $io = null;

    public function __construct(
        protected readonly ParameterBagInterface $parameterBag,
        protected readonly ContaoFramework $framework,
        protected EntityImportConfigModel $configModel,
        protected SourceInterface $source,
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly RequestStack $requestStack,
        protected readonly Connection $conn,
        protected readonly EntityImportUtil $util,
        protected readonly Slug $slug,
        protected readonly HttpClientInterface $httpClient,
        protected readonly InsertTagParser $insertTagParser
    ) {
    }

    public function setInputOutput(SymfonyStyle $io): void
    {
        $this->io = $io;
    }

    public function run(): array
    {
        $this->stopwatch = new Stopwatch();

        $this->stopwatch->start('contao-entity-import-bundle.id'.$this->configModel->id);

        try {
            $items = $this->getDataFromSource();
        } catch (\Exception $e) {
            $this->eventDispatcher->dispatch(new AfterImportEvent([], $this->configModel, $this, $this->source, $this->dryRun), AfterImportEvent::NAME);

            return [
                'state' => 'error',
                'error' => $e->getMessage(),
            ];
        }

        $itemCount = \count($items);

        if ($this->io) {
            $this->io->progressStart($itemCount);
        }

        if ($this->webCronMode) {
            $this->configModel->importProgressTotal = $itemCount;
            $this->configModel->save();
        }

        $event = $this->eventDispatcher->dispatch(new BeforeImportEvent($items, $this->configModel, $this, $this->source, $this->dryRun), BeforeImportEvent::NAME);

        $result = $this->executeImport($event->getItems());

        if ($this->io) {
            $this->io->progressFinish();
        }

        $this->eventDispatcher->dispatch(new AfterImportEvent($items, $this->configModel, $this, $this->source, $this->dryRun), AfterImportEvent::NAME);

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

        $mapping = StringUtil::deserialize($this->configModel->fieldMapping, true);
        $mapping = $this->adjustMappingForDcMultilingual($mapping);
        $mapping = $this->adjustMappingForChangeLanguage($mapping);

        foreach ($items as $item) {
            $mappedItem = $this->applyFieldMappingToSourceItem($item, $mapping);

            if ($localizeLabels) {
                $localizedItem = [];

                foreach ($mappedItem as $field => $value) {
                    $localizedItem[$this->getLocalizedFieldName($field, $this->configModel->targetTable)] = $value;
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

    public function setWebCronMode(bool $webCronMode): void
    {
        $this->webCronMode = $webCronMode;
    }

    public function outputResultMessage(string $message, string $type): void
    {
        if ($this->webCronMode) {
            $this->configModel->refresh();

            if ($this->configModel->importProgressResult) {
                $this->configModel->importProgressResult .= '<br>'.$message;
            } else {
                $this->configModel->importProgressResult = $message;
            }

            $this->configModel->save();
        }

        switch ($type) {
            case static::MESSAGE_TYPE_SUCCESS:
                if ($this->io) {
                    $this->io->success($message);
                } else {
                    Message::addConfirmation($message);
                }
                break;

            case static::MESSAGE_TYPE_ERROR:
                if ($this->io) {
                    $this->io->error($message);
                } else {
                    Message::addError($message);
                }
                break;

            case static::MESSAGE_TYPE_WARNING:
                if ($this->io) {
                    $this->io->warning($message);
                } else {
                    Message::addInfo($message);
                }
                break;
        }
    }

    public function outputFinalResultMessage(array $result): void
    {
        $this->framework->getAdapter(System::class)->loadLanguageFile('tl_entity_import_config');

        if ('error' === $result['state']) {
            $message = $result['error'] ?  'Import failed: '.$result['error'] : $GLOBALS['TL_LANG']['tl_entity_import_config']['error']['errorImport'];

            $this->outputResultMessage($message, static::MESSAGE_TYPE_ERROR);
        } else {
            $count = $result['count'];
            $duration = $result['duration'];

            $duration = str_replace('.', ',', round($duration / 1000, 2));

            if ($count > 0) {
                $message = sprintf(
                    $GLOBALS['TL_LANG']['tl_entity_import_config']['success']['import'] ?? '%d items imported in %s seconds',
                    $count,
                    $duration
                );
                $this->outputResultMessage($message, static::MESSAGE_TYPE_SUCCESS);
            }
        }

        $request = $this->requestStack->getCurrentRequest();
        $redirectUrl = $request?->query->get('redirect_url');

        if ($redirectUrl) {
            throw new RedirectResponseException(html_entity_decode($redirectUrl));
        }
    }

    public function sendErrorEmail(string $errorMessage): void
    {
        $config = $this->getDebugConfig();

        if ($this->configModel->errorNotificationLock || $this->dryRun) {
            return;
        }

        if (isset($config['contao_log']) && $config['contao_log']) {
            $this->framework->getAdapter(System::class)->log($errorMessage, 'Importer::executeImport', LogLevel::ERROR);
        }

        if (isset($config['email']) && $config['email']) {
            $email = new Email();
            $email->subject = sprintf($GLOBALS['TL_LANG']['MSC']['entityImport']['exceptionEmailSubject'], $this->configModel->title);
            $email->text = sprintf('An error occurred on domain "%s"', $this->configModel->cronDomain).' : '.$errorMessage;
            $email->sendTo($this->configModel->errorNotificationEmail ?: $GLOBALS['TL_CONFIG']['adminEmail']);
        }

        $this->conn->update('tl_entity_import_config', ['errorNotificationLock' => '1'], ['id' => $this->configModel->id]);
    }

    public function postProcess(string $table, array $mappedItems, array $dbIdMapping, array $dbItemMapping): void
    {
        // DC_Multilingual -> fix langPid
        if (class_exists('\Terminal42\DcMultilingualBundle\Terminal42DcMultilingualBundle') &&
            $this->configModel->addDcMultilingualSupport) {
            $langPidField = $GLOBALS['TL_DCA'][$table]['config']['langPid'] ?? 'langPid';

            foreach ($dbItemMapping as $itemMapping) {
                if (!$itemMapping['source']['langPid']) {
                    continue;
                }

                if (!$this->dryRun) {
                    $this->conn->update($table, [
                        $langPidField => $dbIdMapping[$itemMapping['source']['langPid']] ?? null
                    ], ['id' => $itemMapping['target']['id']]);
                }
            }
        }

        // Drafts -> fix draftParent
        if (class_exists('\HeimrichHannot\DraftsBundle\ContaoDraftsBundle') &&
            $this->configModel->addDraftsSupport) {
            foreach ($dbItemMapping as $itemMapping) {
                if (!$itemMapping['source']['draftParent']) {
                    continue;
                }

                if (!$this->dryRun) {
                    $this->conn->update($table, [
                        'draftParent' => $dbIdMapping[$itemMapping['source']['draftParent']] ?? null
                    ], ['id' => $itemMapping['target']['id']]);
                }
            }
        }

        // change language -> fix languageMain
        if (class_exists('\Terminal42\ChangeLanguage\Language') && $this->configModel->addChangeLanguageSupport) {
            foreach ($dbItemMapping as $itemMapping) {
                if (!$itemMapping['source']['languageMain']) {
                    continue;
                }

                $result = $this->conn->fetchAssociative(
                    'SELECT id FROM '.$table.' WHERE id = ?',
                    [$dbIdMapping[$itemMapping['source']['languageMain']] ?? 0]
                );

                if (!$this->dryRun && $result) {
                    $this->conn->update($table, [
                        'languageMain' => $result['id']
                    ], ['id' => $itemMapping['target']['id']]);
                }
            }
        }

        $this->deleteAfterImport($mappedItems);
        $this->applySorting();

        $this->conn->commit();
    }

    protected function applyFieldMappingToSourceItem(array $item, array $mapping): ?array
    {
        $mapped = [];

        foreach ($mapping as $mappingElement) {
            if (isset($mappingElement['skip']) && $mappingElement['skip']) {
                continue;
            }

            if ('source_value' === $mappingElement['valueType']) {
                $mapped[$mappingElement['columnName']] = $item[$mappingElement['mappingValue']] ?? null;
            } elseif ('static_value' === $mappingElement['valueType']) {
                $mapped[$mappingElement['columnName']] = $mappingElement['staticValue'] ?? null;
            }

            if (isset($mapped[$mappingElement['columnName']]) && \is_string($mapped[$mappingElement['columnName']]) && !Validator::isBinaryUuid($mapped[$mappingElement['columnName']])) {
                $mapped[$mappingElement['columnName']] = trim($mapped[$mappingElement['columnName']]);
            }
        }

        return $mapped;
    }

    protected function executeImport(array $items): array
    {
        $controller = $this->framework->getAdapter(Controller::class);
        $controller->loadLanguageFile('default');
        $controller->loadLanguageFile('tl_entity_import_config');

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

            $mappedItems = [];

            $mode = $this->configModel->importMode;

            $mapping = StringUtil::deserialize($this->configModel->fieldMapping, true);
            $mapping = $this->adjustMappingForDcMultilingual($mapping);
            $mapping = $this->adjustMappingForChangeLanguage($mapping);

            $dbIdMapping = [];
            $dbItemMapping = [];

            if ('merge' === $mode) {
                $mergeIdentifiers = StringUtil::deserialize($this->configModel->mergeIdentifierFields, true);

                if (empty(array_filter($mergeIdentifiers))) {
                    throw new Exception($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['noIdentifierFields']);
                }

                $this->initDbCacheForMerge(StringUtil::deserialize($this->configModel->mergeIdentifierFields, true));

                $identifierFields = [];

                foreach ($mergeIdentifiers as $mergeIdentifier) {
                    $identifierFields[] = $mergeIdentifier['source'];
                }
            }

            $this->deleteBeforeImport();

            $this->conn->beginTransaction();

            foreach ($items as $item) {
                $mappedItem = $this->applyFieldMappingToSourceItem($item, $mapping);

                $columnsNotExisting = array_diff(array_keys($mappedItem), $targetTableColumns);

                if (!empty($columnsNotExisting)) {
                    throw new Exception(sprintf('Columns do not exist: %s', implode(', ', $columnsNotExisting)));
                }

                $event = $this->eventDispatcher->dispatch(new BeforeItemImportEvent(
                    $mappedItem,
                    $item,
                    $this->configModel,
                    $this,
                    $this->source,
                    false,
                    $this->dryRun
                ), BeforeItemImportEvent::NAME);

                $item = $event->getItem();
                $mappedItem = $event->getMappedItem();

                if ($event->isSkipped()) {
                    if ($this->webCronMode) {
                        $this->configModel->importProgressSkipped++;
                        $this->configModel->save();
                    }
                    continue;
                }

                ++$count;
                $importedRecord = null;

                if ('insert' === $mode) {
                    if (!$this->dryRun) {
                        $this->conn->insert($table, $mappedItem);
                        $record = (object) $mappedItem;
                        $record->id = $this->conn->lastInsertId();

                        $set = $this->setDateAdded($record);
                        $set = array_merge($set, $this->generateAlias($record));
                        $set = array_merge($set, $this->setTstamp($record));
                        $set = array_merge($set, $this->applyFieldFileMapping($record, $mappedItem));

                        if (!empty($set) && !$this->dryRun) {
                            $this->conn->update($table, $set, ['id' => $record->id]);
                        }

                        $importedRecord = $record;
                    }
                } elseif ('merge' === $mode) {
                    $key = implode('||', array_map(fn($field) => $item[$field], $identifierFields));

                    if ($key && isset($this->dbMergeCache[$key])) {
                        $existingId = $this->dbMergeCache[$key];

                        // Fetch the existing row using the DBAL connection
                        $existingRecord = $this->conn->fetchAssociative(
                            'SELECT * FROM '.$table.' WHERE id = ?',
                            [$existingId]
                        );

                        if ($existingRecord) {
                            $this->updateMappingItemForSkippedFields($mappedItem);

                            $existing = (object) $existingRecord;

                            $set = $this->setDateAdded($existing);
                            $set = array_merge($set, $this->generateAlias($existing));
                            $set = array_merge($set, $this->setTstamp($existing));
                            $set = array_merge($set, $this->applyFieldFileMapping($existing, $mappedItem));

                            if (!$this->dryRun) {
                                $this->conn->update($table, array_merge($mappedItem, $set), ['id' => $existing->id]);
                            }

                            $importedRecord = $existing;
                        } else {
                            if (!$this->dryRun) {
                                $this->conn->insert($table, $mappedItem);

                                $record = (object) $mappedItem;
                                $record->id = $this->conn->lastInsertId();

                                $set = $this->setDateAdded($record);
                                $set = array_merge($set, $this->generateAlias($record));
                                $set = array_merge($set, $this->setTstamp($record));
                                $set = array_merge($set, $this->applyFieldFileMapping($record, $mappedItem));

                                if (!empty($set) && !$this->dryRun) {
                                    $this->conn->update($table, $set, ['id' => $existing->id]);
                                }

                                $importedRecord = $record;
                            }
                        }
                    } else {
                        if (!$this->dryRun) {
                            $this->conn->insert($table, $mappedItem);

                            $record = (object) $mappedItem;
                            $record->id = $this->conn->lastInsertId();

                            $set = $this->setDateAdded($record);
                            $set = array_merge($set, $this->generateAlias($record));
                            $set = array_merge($set, $this->setTstamp($record));
                            $set = array_merge($set, $this->applyFieldFileMapping($record, $mappedItem));

                            if (!empty($set) && !$this->dryRun) {
                                $this->conn->update($table, $set, ['id' => $record->id]);
                            }

                            $importedRecord = $record;
                        }
                    }
                } else {
                    throw new Exception($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['modeNotSet']);
                }

                if (isset($item['__id'])) {
                    $dbIdMapping[$item['__id']] = $importedRecord?->id;
                }

                $dbItemMapping[] = [
                    'source' => $item,
                    'target' => (array)$importedRecord,
                ];

                $this->importCategoryAssociations($mapping, $item, $importedRecord?->id);

                $this->eventDispatcher->dispatch(new AfterItemImportEvent(
                    $importedRecord,
                    $item,
                    $mappedItem,
                    $mapping,
                    $this->configModel,
                    $this,
                    $this->source,
                    $this->dryRun
                ), AfterItemImportEvent::NAME);

                $mappedItems[] = $mappedItem;

                if ($this->io) {
                    $this->io->progressAdvance();
                }

                if ($this->webCronMode) {
                    $this->configModel->importProgressCurrent++;
                    $this->configModel->save();
                }
            }

            $this->postProcess($table, $mappedItems, $dbIdMapping, $dbItemMapping);
        } catch (\Exception $e) {
            $this->stopwatch->stop('contao-entity-import-bundle.id'.$this->configModel->id);

            $this->sendErrorEmail($e->getMessage());

            return [
                'state' => 'error',
                'error' => $e->getMessage(),
            ];
        }

        $event = $this->stopwatch->stop('contao-entity-import-bundle.id'.$this->configModel->id);

        $duration = $event->getDuration();

        if ($this->configModel->errorNotificationLock) {
            $this->conn->update('tl_entity_import_config', ['errorNotificationLock' => ''], ['id' => $this->configModel->id]);
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

    protected function updateMappingItemForSkippedFields(array &$mappingItem): void
    {
        if (!$this->configModel->addSkipFieldsOnMerge) {
            return;
        }

        $skipFields = StringUtil::deserialize($this->configModel->skipFieldsOnMerge, true);

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
                    $this->source->getSourceModel()->dbSourceTable,
                    $item['id'],
                    $mappingElement['mappingValue']
                );

                if (null !== $categories) {
                    // Import categories logic
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

        $qb = $this->conn->createQueryBuilder();
        $qb->select('*')
            ->from($table);

        if ($this->configModel->mergeIdentifierAdditionalWhere) {
            $qb->where(html_entity_decode($this->configModel->mergeIdentifierAdditionalWhere));
        }

        $records = $qb->executeQuery()->fetchAllAssociative();

        if (empty($records)) {
            $this->dbMergeCache = [];
            return;
        }

        $identifierFields = array_column($mergeIdentifiers, 'target');

        $cache = [];

        foreach ($records as $record) {
            $key = implode('||', array_map(fn($field) => $record[$field], $identifierFields));

            if (!$key) {
                continue;
            }

            $cache[$key] = $record['id'];
        }

        $this->dbMergeCache = $cache;
    }

    protected function getDebugConfig(): ?array
    {
        if (!$this->parameterBag->has('huh_entity_import')) {
            return null;
        }

        $config = $this->parameterBag->get('huh_entity_import');

        return $config['debug'] ?? [];
    }

    protected function deleteBeforeImport()
    {
        $table = $this->configModel->targetTable;

        if ($this->configModel->deleteBeforeImport && !$this->dryRun) {
            $this->conn->executeStatement(
                'DELETE FROM '.$table.' WHERE '.html_entity_decode($this->configModel->deleteBeforeImportWhere)
            );
        }
    }

    protected function deleteAfterImport(array $mappedItems)
    {
        $table = $this->configModel->targetTable;

        switch ($this->configModel->deletionMode) {
            case EntityImportConfigContainer::DELETION_MODE_MIRROR:
                $deletionIdentifiers = StringUtil::deserialize($this->configModel->deletionIdentifierFields, true);

                if (empty($deletionIdentifiers)) {
                    throw new Exception($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['noIdentifierFields']);
                }

                $conditions = [];

                foreach ($deletionIdentifiers as $deletionIdentifier) {
                    $identifiers = '';

                    foreach ($mappedItems as $value) {
                        if (isset($value[$deletionIdentifier['name']])) {
                            $identifiers .= $this->conn->quote($value[$deletionIdentifier['name']]).',';
                        }
                    }

                    $identifiers = rtrim($identifiers, ',');

                    if ($identifiers) {
                        $conditions[] = '('.$deletionIdentifier['name'].' NOT IN ('.$identifiers.'))';
                    }
                }

                if ($this->configModel->targetDeletionAdditionalWhere) {
                    $conditions[] = '('.html_entity_decode($this->configModel->targetDeletionAdditionalWhere).')';
                }

                if (!$this->dryRun && !empty($conditions)) {
                    $this->conn->executeStatement('DELETE FROM '.$table.' WHERE '.implode(' AND ', $conditions));
                }

                break;

            case EntityImportConfigContainer::DELETION_MODE_TARGET_FIELDS:
                if ($this->configModel->deleteBeforeImport && !$this->dryRun) {
                    $this->conn->executeStatement(
                        'DELETE FROM '.$table.' WHERE '.html_entity_decode($this->configModel->targetDeletionWhere)
                    );
                }

                break;
        }
    }

    protected function setDateAdded($record): array
    {
        $field = $this->configModel->targetDateAddedField;

        if (!$this->configModel->setDateAdded || !$field || $record->{$field} ?? null || !($record->id ?? null)) {
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

        if (!$this->configModel->setTstamp || !$field || !($record->id ?? null)) {
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
        $field = $this->configModel->targetAliasField;
        $fieldPattern = $this->configModel->aliasFieldPattern;

        if (!$this->configModel->generateAlias || !$field || !$fieldPattern || !($record->id ?? null)) {
            return [];
        }

        $aliasBase = preg_replace_callback(
            '@%([^%]+)%@i',
            fn($matches) => $record->{$matches[1]},
            $fieldPattern
        );

        $alias = $this->generateAliasValue(
            $record->{$field} ?? '',
            (int) $record->id,
            $this->configModel->targetTable,
            $field,
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
        $fileMapping = StringUtil::deserialize($this->configModel->fileFieldMapping, true);

        foreach ($fileMapping as $mapping) {
            if (($record->{$mapping['targetField']} ?? null) && $mapping['skipIfExisting']) {
                continue;
            }

            try {
                $content = $this->getFileContent((string) $item[$mapping['mappingField']]);
            } catch (\Exception) {
                $set[$mapping['targetField']] = null;
                continue;
            }

            if (Validator::isUrl($item[$mapping['mappingField']]) && $mapping['delayAfter'] > 0) {
                sleep((int) ($mapping['delayAfter']));
            }

            if (!$content) {
                $set[$mapping['targetField']] = null;
                continue;
            }

            switch ($mapping['namingMode']) {
                case 'random_md5':
                    $filename = md5(random_int(0, 99999999999999));
                    break;

                case 'field_pattern':
                    $filename = preg_replace_callback(
                        '@%([^%]+)%@i',
                        function($matches) use ($record, $item) {
                            $fieldName = $matches[1];
                            return $record->{$fieldName} ?? $item[$fieldName] ?? '';
                        },
                        (string) $mapping['fieldPattern']
                    );

                    $filename = trim($filename);

                    if (empty($filename)) {
                        $filename = 'file_' . ($record->id ?? time());
                    }
                    break;
            }

            if ($mapping['slugFilename']) {
                $filename = $slugGenerator->generate($filename);
            }

            $extension = $this->guessExtensionFromContent($content);

            $extension = $extension ? '.'.$extension : '';

            $folderPath = $this->getPathFromUuid((string) $mapping['targetFolder']);

            if (null === $folderPath) {
                $set[$mapping['targetField']] = null;
                continue;
            }

            $folder = new Folder($folderPath);

            $filenameWithoutExtension = $folder->path.'/'.$filename;

            $file = new File($filenameWithoutExtension.$extension);

            if ($file->exists()) {
                if (!isset($record->{$mapping['targetField']}) || !$record->{$mapping['targetField']}) {
                    $set[$mapping['targetField']] = $file->getModel()->uuid;
                    continue;
                }
            }

            $event = $this->eventDispatcher->dispatch(new BeforeFileImportEvent(
                $file->path,
                $content,
                (array) $record,
                $item,
                $this->configModel,
                $this,
                $this->source,
                $this->dryRun
            ), BeforeFileImportEvent::NAME);

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

        $where = $this->insertTagParser->replace(
            html_entity_decode($this->configModel->targetSortingContextWhere)
        );

        $order = $this->configModel->targetSortingOrder;

        if (!$this->configModel->sortingMode || !$field || !$where || !$order) {
            return;
        }

        $table = $this->configModel->targetTable;

        switch ($this->configModel->sortingMode) {
            case EntityImportConfigContainer::SORTING_MODE_TARGET_FIELDS:
                $qb = $this->conn->createQueryBuilder();
                $results = $qb->select('*')
                    ->from($table)
                    ->where($where)
                    ->orderBy($order)
                    ->executeQuery()
                    ->fetchAllAssociative();

                if (empty($results)) {
                    return;
                }

                $count = 1;

                foreach ($results as $result) {
                    if ($this->dryRun) {
                        continue;
                    }

                    $this->conn->update($table, [
                        $field => $count * 128
                    ], ['id' => $result['id']]);

                    ++$count;
                }

                break;
        }
    }

    protected function adjustMappingForDcMultilingual(array $mapping)
    {
        if (!class_exists('\Terminal42\DcMultilingualBundle\Terminal42DcMultilingualBundle') || !$this->configModel->addDcMultilingualSupport) {
            return $mapping;
        }

        $table = $this->configModel->targetTable;

        $this->framework->getAdapter(Controller::class)->loadDataContainer($table);

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

            if ($dca['config']['langStart'] ?? false) {
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

    private function getLocalizedFieldName(string $field, string $table): string
    {
        $this->framework->getAdapter(Controller::class)->loadDataContainer($table);

        $label = $GLOBALS['TL_DCA'][$table]['fields'][$field]['label'][0] ?? null;

        return $label ?: $field;
    }

    private function generateAliasValue(string $alias, int $id, string $table, string $field, string $aliasBase): string
    {
        $aliasExists = fn(string $value): bool => (bool) $this->conn->fetchOne(
            'SELECT id FROM '.$table.' WHERE '.$field.'=? AND id!=?',
            [$value, $id]
        );

        if (!$alias) {
            return $this->slug->generate($aliasBase, [], $aliasExists);
        }

        if (preg_match('/^[1-9]\d*$/', $alias)) {
            throw new Exception($GLOBALS['TL_LANG']['ERR']['aliasNumeric'] ?? 'Alias cannot be numeric.');
        }

        if ($aliasExists($alias)) {
            throw new Exception($GLOBALS['TL_LANG']['ERR']['aliasExists'] ?? 'Alias already exists.');
        }

        return $alias;
    }

    private function getFileContent(string $value): ?string
    {
        if ('' === $value) {
            return null;
        }

        if (Validator::isUrl($value)) {
            $response = $this->httpClient->request('GET', $value);

            return $response->getContent(false);
        }

        if (StringUtil::isUuid($value)) {
            $fileModel = FilesModel::findByUuid($value);

            if (null === $fileModel) {
                return null;
            }

            $file = new File($fileModel->path);

            return $file->getContent();
        }

        $projectDir = $this->parameterBag->get('kernel.project_dir');
        $path = $value;

        if (!str_starts_with($path, $projectDir)) {
            $path = $projectDir.'/'.$path;
        }

        if (!is_file($path)) {
            return null;
        }

        return file_get_contents($path) ?: null;
    }

    private function guessExtensionFromContent(string $content): ?string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($content);

        if (!$mimeType) {
            return null;
        }

        $extensions = (new MimeTypes())->getExtensions($mimeType);

        return $extensions[0] ?? null;
    }

    private function getPathFromUuid(string $uuid): ?string
    {
        $fileModel = FilesModel::findByUuid($uuid);

        return $fileModel?->path;
    }

}
