<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Event;

use Contao\Model;
use Symfony\Component\EventDispatcher\Event;

class AddSourceFieldMappingPresetsEvent extends Event
{
    public const NAME = 'huh.entity_import.add_source_field_mapping_presets';

    /**
     * @var array
     */
    protected $presets;
    /**
     * @var Model
     */
    protected $sourceModel;

    public function __construct(array $presets, Model $sourceModel)
    {
        $this->presets = $presets;
        $this->sourceModel = $sourceModel;
    }

    public function getPresets(): array
    {
        return $this->presets;
    }

    public function setPresets(array $presets)
    {
        $this->presets = $presets;
    }

    public function getSourceModel(): Model
    {
        return $this->sourceModel;
    }

    public function setSourceModel(Model $sourceModel): void
    {
        $this->sourceModel = $sourceModel;
    }
}
