<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
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

    protected Model $configModel;
    protected SourceInterface $source;
    protected array $mappedItem;
    protected array $item;
    protected $importedRecord;
    protected bool $dryRun;
    protected array $mapping;

    public function __construct($importedRecord, array $mappedItem, array $item, array $mapping, Model $configModel, SourceInterface $source, bool $dryRun = false)
    {
        $this->configModel = $configModel;
        $this->source = $source;
        $this->mappedItem = $mappedItem;
        $this->item = $item;
        $this->importedRecord = $importedRecord;
        $this->dryRun = $dryRun;
        $this->mapping = $mapping;
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

    public function isDryRun(): bool
    {
        return $this->dryRun;
    }

    public function getMapping(): array
    {
        return $this->mapping;
    }
}
