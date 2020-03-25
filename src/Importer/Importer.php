<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Importer;

use Contao\Database;
use HeimrichHannot\EntityImportBundle\Event\AfterImportEvent;
use HeimrichHannot\EntityImportBundle\Event\BeforeImportEvent;
use HeimrichHannot\EntityImportBundle\Source\SourceInterface;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Importer implements ImporterInterface
{
    /**
     * @var SourceInterface
     */
    protected $source;
    protected $dryRun;
    protected $mergeTable;

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;
    private $targetTable;
    /**
     * @var DatabaseUtil
     */
    private $databaseUtil;

    /**
     * Importer constructor.
     *
     * @param $databaseUtil DatabaseUtil
     * @param $targetTable string
     * @param $eventDispatcher EventDispatcher
     */
    public function __construct(EventDispatcher $eventDispatcher, DatabaseUtil $databaseUtil)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->databaseUtil = $databaseUtil;
    }

    public function init(SourceInterface $source, string $targetTable)
    {
        $this->database = Database::getInstance();
        $this->source = $source;
        $this->targetTable = $targetTable;
    }

    /**
     * {@inheritdoc}
     */
    public function run(bool $dry = false, bool $mergeTable = false): bool
    {
        $this->dryRun = $dry;
        $this->mergeTable = $mergeTable;
        $items = $this->getDataFromSource();

        $this->eventDispatcher->dispatch(BeforeImportEvent::NAME, new BeforeImportEvent());

        if ($this->database->tableExists($this->targetTable)) {
            foreach ($items as $item) {
                $this->databaseUtil->insert($this->targetTable, $item);
            }
        }

        $this->eventDispatcher->dispatch(AfterImportEvent::NAME, new AfterImportEvent());

        return true;
    }

    public function getDataFromSource(): array
    {
        return $this->source->applyMapping();
    }
}
