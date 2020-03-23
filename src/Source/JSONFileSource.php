<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

class JSONFileSource extends FileSource
{
    public function getFile(): string
    {
        return $this->filePath;
    }

    public function getMapping(): array
    {
        return $this->fileMapping;
    }

    public function getData(): array
    {
        $data = [];
        $fileContent = file_get_contents($this->filePath);

        if (null !== $fileContent) {
            $data = json_decode($fileContent);
        }

        return $data;
    }

    public function applyMapping(): array
    {
        // TODO: apply delimiter for large datasets, with progression bar (split data into pieces, this should be configurable in the backend)

        $fileData = $this->getData();
        $data = [];
        if (null !== $fileData) {
            foreach ($fileData as $index => $dataElement) {
                foreach ($this->fileMapping as $mappingElement) {
                    $arrElementMapping = explode('.', $mappingElement['value']);

                    $data[$index][$mappingElement['name']] = $this->getValueFromMapping($dataElement, $arrElementMapping);
                }
            }
        }

        return $data;
    }

    private function getValueFromMapping($data, $mapping)
    {
        if (null === $mapping) {
            return $data;
        }
        $data = $data[array_pop($mapping)];

        return $this->getValueFromMapping($data, $mapping);
    }
}
