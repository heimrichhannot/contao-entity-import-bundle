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

class BeforeItemImportEvent extends Event
{
    public const NAME = 'huh.entity_import.before_item_import_event';

    protected Model $configModel;
    protected SourceInterface $source;
    protected array $mappedItem;
    protected array $item;
    protected bool $skipped;
    protected bool $dryRun;

    public function __construct(array $mappedItem, array $item, Model $configModel, SourceInterface $source, bool $skipped = false, bool $dryRun = false)
    {
        $this->configModel = $configModel;
        $this->source = $source;
        $this->mappedItem = $mappedItem;
        $this->item = $item;
        $this->skipped = $skipped;
        $this->dryRun = $dryRun;
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

    public function isSkipped(): bool
    {
        return $this->skipped;
    }

    public function setSkipped(bool $skipped): void
    {
        $this->skipped = $skipped;
    }

    public function isDryRun(): bool
    {
        return $this->dryRun;
    }
}
