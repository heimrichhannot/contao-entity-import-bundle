<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

use HeimrichHannot\EntityImportBundle\Model\EntityImportSourceModel;

abstract class Source implements SourceInterface
{
    /**
     * @var array
     */
    protected $fieldMapping;

    /**
     * @var EntityImportSourceModel
     */
    protected $sourceModel;

    public function __construct(EntityImportSourceModel $sourceModel)
    {
        $this->sourceModel = $sourceModel;
        $this->fieldMapping = $sourceModel->fieldMapping;
    }

    public function getMapping(): array
    {
        return $this->fieldMapping;
    }
}
