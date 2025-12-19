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

class BeforeImportEvent extends Event
{
    public const NAME = 'huh.entity_import.before_import_event';

    public function __construct(protected array $items, protected Model $configModel, protected ImporterInterface $importer, protected SourceInterface $source, protected bool $dryRun = false, protected array $options = [])
    {
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function setItems(array $items)
    {
        return $this->items = $items;
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

    public function getImporter(): ImporterInterface
    {
        return $this->importer;
    }
}
