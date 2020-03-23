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
use Symfony\Component\EventDispatcher\EventDispatcher;

class Importer implements ImporterInterface
{
    protected $source;
    protected $dryRun;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;
    private $database;
    private $targetTable;

    /**
     * Importer constructor.
     *
     * @param $source SourceInterface
     * @param $database Database
     * @param $targetTable string
     * @param $eventDispatcher EventDispatcher
     */
    public function __construct(SourceInterface $source, Database $database, string $targetTable, EventDispatcher $eventDispatcher)
    {
        $this->source = $source;
        $this->database = $database;
        $this->targetTable = $targetTable;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getDataFromSource(SourceInterface $source): array
    {
        return $this->source->applyMapping();
    }

    /**
     * {@inheritdoc}
     */
    public function run($dry = false)
    {
        $this->dryRun = $dry;
        $items = $this->getDataFromSource($this->source);

        $this->eventDispatcher->dispatch(BeforeImportEvent::NAME, new BeforeImportEvent());

        if ($this->database->tableExists($this->targetTable)) {
            foreach ($items as $item) {
                $query = $this->prepareQuery($item);
                $this->database->prepare($query)->execute();
            }
        }

        $this->eventDispatcher->dispatch(AfterImportEvent::NAME, new AfterImportEvent());

        return true;
    }

    public function prepareQuery(array $data): string
    {
        return 'INSERT INTO '.$this->targetTable.' ('.implode(',', array_keys($data)).') VALUES ('.implode(',', array_map(function ($val) { return "'".str_replace("'", "''", $val)."'"; }, array_values($data))).')';
    }
}
