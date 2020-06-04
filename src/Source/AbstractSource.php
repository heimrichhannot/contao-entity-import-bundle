<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

use Contao\Model;

abstract class AbstractSource implements SourceInterface
{
    /**
     * @var array
     */
    protected $fieldMapping;

    /**
     * @var Model
     */
    protected $sourceModel;

    public function __construct()
    {
    }

    public function getMapping(): array
    {
        return $this->fieldMapping;
    }

    public function setFieldMapping(array $mapping)
    {
        $this->fieldMapping = $mapping;
    }

    public function getSourceModel(): Model
    {
        return $this->sourceModel;
    }

    public function setSourceModel(Model $sourceModel)
    {
        $this->sourceModel = $sourceModel;
    }
}
