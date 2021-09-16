<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Event;

use Contao\Model;
use HeimrichHannot\EntityImportBundle\Source\SourceInterface;
use Symfony\Contracts\EventDispatcher\Event;

class SourceFactoryCreateSourceEvent extends Event
{
    public const NAME = 'huh.entity_import.source_factory_create_source_event';

    private SourceInterface $source;
    private Model $sourceModel;

    public function __construct(?SourceInterface $source, Model $sourceModel)
    {
        $this->source = $source;
        $this->sourceModel = $sourceModel;
    }

    public function getSource(): ?SourceInterface
    {
        return $this->source;
    }

    public function setSource(SourceInterface $source)
    {
        $this->source = $source;
    }

    public function getSourceModel(): Model
    {
        return $this->sourceModel;
    }
}
