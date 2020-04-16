<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Importer;

use Contao\Database;
use Contao\Message;
use Contao\Model;
use Database\Result;
use HeimrichHannot\EntityImportBundle\Event\AfterImportEvent;
use HeimrichHannot\EntityImportBundle\Event\BeforeImportEvent;
use HeimrichHannot\EntityImportBundle\Model\EntityImportConfigModel;
use HeimrichHannot\EntityImportBundle\Source\SourceInterface;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\String\StringUtil;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * Importer constructor.
     */
    public function __construct(Model $configModel, SourceInterface $source, EventDispatcherInterface $eventDispatcher, DatabaseUtil $databaseUtil, ModelUtil $modelUtil, StringUtil $stringUtil, DcaUtil $dcaUtil)
    {
        $this->configModel = $configModel;
        $this->source = $source;
        $this->databaseUtil = $databaseUtil;
        $this->eventDispatcher = $eventDispatcher;
        $this->modelUtil = $modelUtil;
        $this->stringUtil = $stringUtil;
        $this->dcaUtil = $dcaUtil;
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

    public function applyFieldMappingToSourceItem(array $item): array
    {
        $fields = \Contao\StringUtil::deserialize($this->configModel->fieldMapping);

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
        $database = Database::getInstance();
        $table = $this->configModel->targetTable;

        if (!$database->tableExists($table)) {
            new Exception($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['tableDoesNotExist']);
        }

        try {
            $count = 0;
            $targetTableColumns = $database->getFieldNames($table);

            $mode = $this->configModel->importMode;

            if ('insert' === $mode && $this->configModel->purgeBeforeImport) {
                $this->databaseUtil->delete($table, $this->configModel->purgeWhereClause);
            }

            foreach ($items as $item) {
                $item = $this->applyFieldMappingToSourceItem($item);

                $columnsNotExisting = array_diff(array_keys($item), $targetTableColumns);

                if (!empty($columnsNotExisting)) {
                    throw new Exception($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['tableFieldsDiffer']);
                }

                ++$count;

                if ('insert' === $mode) {
                    if (!$this->dryRun) {
                        $statement = $this->databaseUtil->insert($table, $item);

                        if (null !== ($record = $this->databaseUtil->findResultByPk($table, $statement->insertId))) {
                            $this->generateAlias($record);
                            $this->setDateAdded($record);
                            $this->setTstamp($record);
                        }
                    }
                } elseif ('merge' === $mode) {
                    $mergeIdentifiers = \Contao\StringUtil::deserialize($this->configModel->mergeIdentifierFields, true);

                    if (empty($mergeIdentifiers)) {
                        throw new Exception($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['noIdentifierFields']);
                    }

                    $columns = [];
                    $values = [];

                    foreach ($mergeIdentifiers as $mergeIdentifier) {
                        $columns[] = '('.$table.'.'.$mergeIdentifier['target'].'=?)';
                        $values[] = $item[$mergeIdentifier['source']];
                    }

                    $existing = $this->databaseUtil->findOneResultBy($table, $columns, $values);

                    if ($existing->numRows > 0) {
                        if (!$this->dryRun) {
                            $this->databaseUtil->update($table, $item, implode(' AND ', $columns), $values);
                        }

                        $this->generateAlias($existing);
                        $this->setDateAdded($existing);
                        $this->setTstamp($existing);
                    } else {
                        if (!$this->dryRun) {
                            $statement = $this->databaseUtil->insert($table, $item);

                            if (null !== ($record = $this->databaseUtil->findResultByPk($table, $statement->insertId))) {
                                $this->generateAlias($record);
                                $this->setDateAdded($record);
                                $this->setTstamp($record);
                            }
                        }
                    }
                } else {
                    throw new Exception($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['modeNotSet']);
                }
            }

            if ($count > 0) {
                Message::addConfirmation(sprintf($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['successfulImport'], $count));
            } else {
                Message::addInfo(sprintf($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['emptyFile']));
            }
        } catch (\Exception $e) {
            Message::addError(sprintf($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['errorImport'], $count, $e->getMessage()));
        }
    }

    protected function setDateAdded(Result $record)
    {
        $table = $this->configModel->targetTable;
        $field = $this->configModel->dateAddedField;

        if (!$this->configModel->setDateAdded || !$field || $record->{$field} || !$record->id) {
            return;
        }

        if (!$this->dryRun) {
            $this->databaseUtil->update($table, [
                $field => time(),
            ], "$table.id=?", [$record->id]);
        }
    }

    protected function setTstamp(Result $record)
    {
        $table = $this->configModel->targetTable;
        $field = $this->configModel->tstampField;

        if (!$this->configModel->setTstamp || !$field || !$record->id) {
            return;
        }

        if (!$this->dryRun) {
            $this->databaseUtil->update($table, [
                $field => time(),
            ], "$table.id=?", [$record->id]);
        }
    }

    protected function generateAlias(Result $record)
    {
        $table = $this->configModel->targetTable;
        $field = $this->configModel->aliasField;
        $fieldPattern = $this->configModel->aliasFieldPattern;

        if (!$this->configModel->generateAlias || !$field || !$fieldPattern || !$record->id) {
            return;
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

        if (!$this->dryRun) {
            $this->databaseUtil->update($table, [
                $field => $alias,
            ], "$table.id=?", [$record->id]);
        }
    }
}
