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
        $fileData = $this->getData($this->sourceModel->pathToDataArray);
        $data = [];
        if (null !== $fileData) {
            foreach ($fileData as $index => $dataElement) {
                foreach ($this->fieldMapping as $mappingElement) {
                    $arrElementMapping = explode('.', $mappingElement['value']);

                    $data[$index][$mappingElement['name']] = $this->getValueFromMapping($dataElement, $arrElementMapping);
                }
            }
        }

        return $data;
    }

    protected function getValueFromMapping($data, $mapping)
    {
        if (null === $mapping) {
            return $data;
        }
        $data = $data[array_pop($mapping)];

        return $this->getValueFromMapping($data, $mapping);
    }
}
