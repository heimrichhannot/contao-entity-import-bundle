<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

class JSONFileSource extends FileSource
{
    public function getMappedData(): array
    {
        $fileContent = $this->getFileContent();

        $arrPath = explode('.', $this->sourceModel->pathToDataArray);

        $fileData = json_decode($fileContent, true);

        if (empty($arrPath)) {
            $fileData = $this->getDataFromPath($fileData, $arrPath);
        }

        $arrData = [];
        $arrMapping = unserialize($this->sourceModel->fieldMapping);
        if (null !== $fileData) {
            foreach ($fileData as $index => $arrElement) {
                $arrData[] = $this->getMappedValues($arrElement, $arrMapping);
            }
        }

        return $arrData;
    }

    protected function getDataFromPath(array $arrData, array $arrPath): array
    {
        if (empty($arrPath)) {
            return $arrData;
        }

        $arrData = $arrData[array_pop($arrPath)];

        return $this->getDataFromPath($arrData, $arrPath);
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
