<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Event;

use Contao\Model;
use HeimrichHannot\EntityImportBundle\Importer\ImporterInterface;
use HeimrichHannot\EntityImportBundle\Source\SourceInterface;
use Symfony\Contracts\EventDispatcher\Event;

class AfterItemImportEvent extends Event
{
    public const NAME = 'huh.entity_import.after_item_import_event';

    public function __construct(protected $importedRecord, protected array $mappedItem, protected array $item, protected array $mapping, protected Model $configModel, protected ImporterInterface $importer, protected SourceInterface $source, protected bool $dryRun = false)
    {
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

    public function getImporter(): ImporterInterface
    {
        return $this->importer;
    }
}
