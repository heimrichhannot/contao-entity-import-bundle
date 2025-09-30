<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

use Contao\StringUtil;

class XmlFileSource extends AbstractFileSource
{
    public function getMappedData(array $options = []): array
    {
        $fileContent = $this->getFileContent();

        $path = explode('.', $this->sourceModel->pathToDataArray);

        $xml = simplexml_load_string($fileContent);
        $json = json_encode($xml);
        $fileData = json_decode($json, true);

        if (!empty($path)) {
            $fileData = $this->getDataFromPath($fileData, $path);
        }

        $data = [];
        $mapping = StringUtil::deserialize($this->sourceModel->fieldMapping, true);

        if (null !== $fileData) {
            foreach ($fileData as $index => $element) {
                $data[] = $this->getMappedItemData($element, $mapping);
            }
        }

        return $data;
    }

    protected function getDataFromPath(array $data, array $path): array
    {
        if (empty($path) || '' == reset($path)) {
            return $data;
        }

        $data = $data[array_shift($path)];

        return $this->getDataFromPath($data, $path);
    }

    protected function getMappedItemData(?array $element, array $mapping): array
    {
        $result = [];

        foreach ($mapping as $mappingElement) {
            if ('static_value' === $mappingElement['valueType']) {
                $result[$mappingElement['name']] = $this->insertTagParser->replace($mappingElement['staticValue']);
            } elseif ('source_value' === $mappingElement['valueType']) {
                $result[$mappingElement['name']] = $this->getValue($element, $mappingElement);
            }
        }

        return $result;
    }

    /**
     * @param mixed $data this argument can be string or array accordingly to the recursive implementation
     *
     * @return mixed
     */
    protected function getValue($data, array $mapping)
    {
        if (\array_key_exists('sourceValue', $mapping)) {
            $mapping = explode('.', $mapping['sourceValue']);
        }

        if (empty($mapping)) {
            return $data;
        }

        $data = $data[array_shift($mapping)];

        return $this->getValue($data, $mapping);
    }
}
