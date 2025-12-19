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

    public function __construct(private ?SourceInterface $source, private readonly Model $sourceModel)
    {
    }

    public function getSource(): ?SourceInterface
    {
        return $this->source;
    }

    public function setSource(SourceInterface $source): void
    {
        $this->source = $source;
    }

    public function getSourceModel(): Model
    {
        return $this->sourceModel;
    }
}
