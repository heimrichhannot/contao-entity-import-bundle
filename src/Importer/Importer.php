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
use HeimrichHannot\EntityImportBundle\Event\AfterImportEvent;
use HeimrichHannot\EntityImportBundle\Event\BeforeImportEvent;
use HeimrichHannot\EntityImportBundle\Model\EntityImportConfigModel;
use HeimrichHannot\EntityImportBundle\Source\SourceInterface;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\EventDispatcher\EventDispatcher;

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
     * @var bool
     */
    protected $mergeTable;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var string
     */
    protected $targetTable;

    /**
     * @var bool
     */
    protected $isInitialized;

    /**
     * @var bool
     */
    protected $purgeTableBeforeImport;

    /**
     * @var DatabaseUtil
     */
    private $databaseUtil;

    /**
     * @var ModelUtil
     */
    private $modelUtil;

    /**
     * Importer constructor.
     */
    public function __construct(DatabaseUtil $databaseUtil, EventDispatcher $eventDispatcher, Model $configModel, ModelUtil $modelUtil, SourceInterface $source)
    {
        $this->databaseUtil = $databaseUtil;
        $this->eventDispatcher = $eventDispatcher;
        $this->configModel = $configModel;
        $this->modelUtil = $modelUtil;
        $this->source = $source;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): bool
    {
        $items = $this->getDataFromSource();

        $event = $this->eventDispatcher->dispatch(BeforeImportEvent::NAME, new BeforeImportEvent($items));

        $this->executeImport($event->getItems());

        $this->eventDispatcher->dispatch(AfterImportEvent::NAME, new AfterImportEvent($items));

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

    protected function executeImport($items)
    {
        $database = Database::getInstance();

        if (!$database->tableExists($this->configModel->targetTable)) {
            new Exception($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['tableDoNotExist']);
        }

        try {
            $count = 0;
            $targetTableColumns = $database->getFieldNames($this->configModel->targetTable);

            if ($this->configModel->purgeBeforeImport) {
                $this->databaseUtil->delete($this->configModel->targetTable, $this->configModel->purgeWhereClause);
            }

            $mode = $this->configModel->importMode;

            foreach ($items as $item) {
                $columnsNotExisting = array_diff(array_keys($item), $targetTableColumns);
                if (!empty($columnsNotExisting)) {
                    throw new Exception($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['tableFieldsDiffer']);
                }

                ++$count;

                if ($this->dryRun) {
                    continue;
                }

                if ('insert' === $mode) {
                    $this->databaseUtil->insert($this->configModel->targetTable, $item);
                } elseif ('merge' === $mode) {
                    $mergeIdentifier = unserialize($this->configModel->mergeIdentifierFields)[0];
                    if (empty($mergeIdentifier)) {
                        throw new Exception($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['noIdentifierFields']);
                    }
                    $this->databaseUtil->update($this->configModel->targetTable, $item, $mergeIdentifier['target'].'=?', [$mergeIdentifier['source']]);
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
}
