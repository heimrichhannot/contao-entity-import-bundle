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

class BeforeImportEvent extends Event
{
    public const NAME = 'huh.entity_import.before_import_event';

    /**
     * @var array
     */
    protected $items;
    /**
     * @var Model
     */
    protected $configModel;
    /**
     * @var SourceInterface
     */
    protected $source;
    /**
     * @var bool
     */
    private $dryRun;

    /**
     * BeforeImportEvent constructor.
     */
    public function __construct(array $items, Model $configModel, SourceInterface $source, bool $dryRun = false)
    {
        $this->items = $items;
        $this->configModel = $configModel;
        $this->source = $source;
        $this->dryRun = $dryRun;
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
}
