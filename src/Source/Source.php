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

    public function getMappedData(): array
    {
        $fileData = $this->getData();
        $data = [];
        $arrMapping = unserialize($this->fieldMapping);
        if (null !== $fileData) {
            foreach ($fileData as $index => $arrElement) {
                $data[$index] = $this->getMappedValues($arrElement, $arrMapping);
            }
        }

        return $data;
    }

    protected function getMappedValues($arrElement, $arrMapping)
    {
        $arrResult = [];

        foreach ($arrMapping as $mappingElement) {
            $arrMappingElement = explode('.', $mappingElement['value']);

            $arrResult[$mappingElement['name']] = $this->getValue($arrElement, $arrMappingElement);
        }

        return $arrResult;
    }

    protected function getValue($data, $mapping)
    {
        if (empty($mapping)) {
            return $data;
        }

        $data = $data[array_shift($mapping)];

        return $this->getValue($data, $mapping);
    }
}