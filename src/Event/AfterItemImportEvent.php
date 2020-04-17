<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Event;

use Contao\Model;
use HeimrichHannot\EntityImportBundle\Source\SourceInterface;
use Symfony\Component\EventDispatcher\Event;

class AfterItemImportEvent extends Event
{
    public const NAME = 'huh.entity_import.after_item_import_event';

    /**
     * @var Model
     */
    protected $configModel;
    /**
     * @var SourceInterface
     */
    protected $source;
    /**
     * @var array
     */
    private $mappedItem;
    /**
     * @var array
     */
    private $item;
    private $importedRecord;

    /**
     * BeforeImportEvent constructor.
     */
    public function __construct($importedRecord, array $mappedItem, array $item, Model $configModel, SourceInterface $source)
    {
        $this->configModel = $configModel;
        $this->source = $source;
        $this->mappedItem = $mappedItem;
        $this->item = $item;
        $this->importedRecord = $importedRecord;
    }

    public function getConfigModel(): Model
    {
        return $this->configModel;
    }

    public function setConfigModel(Model $configModel): void
    {
        $this->configModel = $configModel;
    }

    public function getSource(): SourceInterface
    {
        return $this->source;
    }

    public function setSource(SourceInterface $source): void
    {
        $this->source = $source;
    }

    public function getMappedItem(): array
    {
        return $this->mappedItem;
    }

    public function setMappedItem(array $mappedItem): void
    {
        $this->mappedItem = $mappedItem;
    }

    public function getItem(): array
    {
        return $this->item;
    }

    public function setItem(array $item): void
    {
        $this->item = $item;
    }

    /**
     * @return mixed
     */
    public function getImportedRecord()
    {
        return $this->importedRecord;
    }

    /**
     * @param mixed $importedRecord
     */
    public function setImportedRecord($importedRecord): void
    {
        $this->importedRecord = $importedRecord;
    }
}
