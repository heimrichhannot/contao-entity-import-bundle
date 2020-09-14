<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Event;

use Contao\Model;
use Symfony\Component\EventDispatcher\Event;

class AddConfigFieldMappingPresetsEvent extends Event
{
    public const NAME = 'huh.entity_import.add_config_field_mapping_presets';

    /**
     * @var array
     */
    protected $presets;
    /**
     * @var Model
     */
    protected $configModel;

    public function __construct(array $presets, Model $configModel)
    {
        $this->presets = $presets;
        $this->configModel = $configModel;
    }

    public function getPresets(): array
    {
        return $this->presets;
    }

    public function setPresets(array $presets)
    {
        $this->presets = $presets;
    }

    public function getConfigModel(): Model
    {
        return $this->configModel;
    }

    public function setConfigModel(Model $configModel): void
    {
        $this->configModel = $configModel;
    }
}
