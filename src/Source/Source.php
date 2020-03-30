<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

use HeimrichHannot\UtilsBundle\Model\ModelUtil;

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
    /**
     * @var ModelUtil
     */
    private $modelUtil;

    public function __construct(ModelUtil $modelUtil)
    {
        $this->modelUtil = $modelUtil;
    }

    public function getMapping(): array
    {
        return $this->fieldMapping;
    }

    public function setFieldMapping($mapping): bool
    {
        $this->fieldMapping = $mapping;
    }
}
