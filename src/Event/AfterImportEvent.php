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

class AfterImportEvent extends Event
{
    public const NAME = 'huh.entity_import.after_import_event';

    protected array $items;
    protected Model $configModel;
    protected SourceInterface $source;
    protected bool $dryRun;
    protected array $options;

    public function __construct(array $items, Model $configModel, SourceInterface $source, bool $dryRun = false, array $options = [])
    {
        $this->items = $items;
        $this->configModel = $configModel;
        $this->source = $source;
        $this->dryRun = $dryRun;
        $this->options = $options;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function setItems(array $items)
    {
        $this->items = $items;
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

    public function isDryRun(): bool
    {
        return $this->dryRun;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
