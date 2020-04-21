<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Importer;

use Contao\Database;
use Contao\Email;
use Contao\Message;
use Contao\Model;
use Database\Result;
use HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer;
use HeimrichHannot\EntityImportBundle\Event\AfterImportEvent;
use HeimrichHannot\EntityImportBundle\Event\AfterItemImportEvent;
use HeimrichHannot\EntityImportBundle\Event\BeforeImportEvent;
use HeimrichHannot\EntityImportBundle\Event\BeforeItemImportEvent;
use HeimrichHannot\EntityImportBundle\Model\EntityImportConfigModel;
use HeimrichHannot\EntityImportBundle\Source\SourceInterface;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
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
     * Importer constructor.
     */
    public function __construct(ContainerInterface $container, Model $configModel, SourceInterface $source, EventDispatcherInterface $eventDispatcher, DatabaseUtil $databaseUtil, ModelUtil $modelUtil, StringUtil $stringUtil, DcaUtil $dcaUtil, ContainerUtil $containerUtil)
    {
        $this->container = $container;
        $this->configModel = $configModel;
        $this->source = $source;
        $this->databaseUtil = $databaseUtil;
        $this->eventDispatcher = $eventDispatcher;
        $this->modelUtil = $modelUtil;
        $this->stringUtil = $stringUtil;
        $this->dcaUtil = $dcaUtil;
        $this->containerUtil = $containerUtil;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): bool
    {
        $items = $this->getDataFromSource();

        $event = $this->eventDispatcher->dispatch(BeforeImportEvent::NAME, new BeforeImportEvent($items, $this->configModel, $this->source));

        $this->executeImport($event->getItems());

        $this->eventDispatcher->dispatch(AfterImportEvent::NAME, new AfterImportEvent($items, $this->configModel, $this->source));

        return true;
    }

    public function getDataFromSource(): array
    {
        return $this->source->getMappedData();
    }

    public function setDryRun(bool $dry)
    {
        $this->dryRun = $dry;
    }

    protected function applyFieldMappingToSourceItem(array $item): ?array
    {
        if (null === $fields = \Contao\StringUtil::deserialize($this->configModel->fieldMapping)) {
            return null;
        }

        $mapped = [];

        foreach ($fields as $field) {
            if ('source_value' === $field['valueType']) {
                $mapped[$field['columnName']] = $item[$field['mappingValue']];
            } elseif ('static_value' === $field['valueType']) {
                $mapped[$field['columnName']] = $this->stringUtil->replaceInsertTags($field['staticValue']);
            }
        }

        return $mapped;
    }

    protected function executeImport(array $items)
    {
        $stopwatch = new Stopwatch();

        $stopwatch->start('contao-entity-import-bundle.id'.$this->configModel->id);

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

            $this->deleteBeforeImport();

            foreach ($items as $item) {
                $mappedItem = $this->applyFieldMappingToSourceItem($item);

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
                    false
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

                        if (null !== ($record = $this->databaseUtil->findResultByPk($table, $statement->insertId))) {
                            $set = $this->setDateAdded($record);
                            $set = array_merge($set, $this->generateAlias($record));
                            $set = array_merge($set, $this->setTstamp($record));

                            if (!empty($set) && !$this->dryRun) {
                                $this->databaseUtil->update($table, $set, "$table.id=?", [$record->id]);
                            }
                        }

                        $importedRecord = $record;
                    }
                } elseif ('merge' === $mode) {
                    $mergeIdentifiers = \Contao\StringUtil::deserialize($this->configModel->mergeIdentifierFields, true);

                    if (empty(array_filter($mergeIdentifiers))) {
                        throw new Exception($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['noIdentifierFields']);
                    }

                    $columns = [];
                    $values = [];

                    foreach ($mergeIdentifiers as $mergeIdentifier) {
                        $columns[] = '('.$table.'.'.$mergeIdentifier['target'].'=?)';
                        $values[] = $mappedItem[$mergeIdentifier['source']];
                    }

                    $existing = $this->databaseUtil->findOneResultBy($table, $columns, $values);

                    if ($existing->numRows > 0) {
                        $set = $this->setDateAdded($existing);
                        $set = array_merge($set, $this->generateAlias($existing));
                        $set = array_merge($set, $this->setTstamp($existing));

                        if (!empty($set) && !$this->dryRun) {
                            $this->databaseUtil->update($table, $set, "$table.id=?", [$existing->id]);
                        }

                        $importedRecord = $existing;
                    } else {
                        if (!$this->dryRun) {
                            $statement = $this->databaseUtil->insert($table, $mappedItem);

                            if (null !== ($record = $this->databaseUtil->findResultByPk($table, $statement->insertId))) {
                                $set = $this->setDateAdded($record);
                                $set = array_merge($set, $this->generateAlias($record));
                                $set = array_merge($set, $this->setTstamp($record));

                                if (!empty($set) && !$this->dryRun) {
                                    $this->databaseUtil->update($table, $set, "$table.id=?", [$record->id]);
                                }
                            }

                            $importedRecord = $record;
                        }
                    }
                } else {
                    throw new Exception($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['modeNotSet']);
                }

                /* @var AfterItemImportEvent $event */
                $this->eventDispatcher->dispatch(AfterItemImportEvent::NAME, new AfterItemImportEvent(
                    $importedRecord,
                    $mappedItem,
                    $item,
                    $this->configModel,
                    $this->source
                ));

                $mappedItems[] = $event->getMappedItem();
            }

            $this->deleteAfterImport($mappedItems);
            $this->applySorting();

            $event = $stopwatch->stop('contao-entity-import-bundle.id'.$this->configModel->id);

            if ($count > 0) {
                $duration = str_replace('.', ',', round($event->getDuration() / 1000, 2));

                Message::addConfirmation(sprintf($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['successfulImport'], $count, $duration));
            } else {
                Message::addInfo(sprintf($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['emptyFile']));
            }

            if ($this->configModel->errorNotificationLock) {
                $this->databaseUtil->update('tl_entity_import_config', ['errorNotificationLock' => '0'], 'tl_entity_import_config.id=?', [$this->configModel->id]);
            }
        } catch (\Exception $e) {
            $config = $this->getDebugConfig();

            if (isset($config['contao_log']) && $config['contao_log']) {
                if (!$this->configModel->errorNotificationLock) {
                    $this->containerUtil->log($e, 'executeImport', LogLevel::ERROR);
                }
            }

            if (isset($config['email']) && $config['email']) {
                if (!$this->configModel->errorNotificationLock) {
                    $email = new Email();
                    $email->subject = sprintf($GLOBALS['TL_LANG']['MSG']['entityImport']['exceptionEmailSubject'], $this->configModel->title);
                    $email->text = sprintf('An error occurred on domain "%s"', $this->configModel->cronDomain).' : '.$e->getMessage();
                    $email->sendTo($GLOBALS['TL_CONFIG']['adminEmail']);
                }
            }

            if (!$this->configModel->errorNotificationLock) {
                $this->databaseUtil->update('tl_entity_import_config', ['errorNotificationLock' => '1'], 'tl_entity_import_config.id=?', [$this->configModel->id]);
            }

            Message::addError(sprintf($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['errorImport'], $count,
                $this->containerUtil->isDev() ? str_replace("\n", '<br>', $e) : $e->getMessage()));
        }
    }

    protected function getDebugConfig(): array
    {
        $config = $this->container->getParameter('huh_entity_import');

        return $config['debug'];
    }

    protected function deleteBeforeImport()
    {
        $table = $this->configModel->targetTable;

        if ($this->configModel->deleteBeforeImport && !$this->dryRun) {
            $this->databaseUtil->delete($table, $this->configModel->targetDeletionWhere);
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

                $conditions[] = '('.$this->configModel->targetDeletionAdditionalWhere.')';

                if (!$this->dryRun) {
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

    protected function setDateAdded(Result $record): array
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

    protected function setTstamp(Result $record): array
    {
        $table = $this->configModel->targetTable;
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

    protected function generateAlias(Result $record): array
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
}
