<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

class JSONFileSource extends FileSource
{
    public function getData(string $path): array
    {
        $fileContent = file_get_contents($this->filePath);

        $arrPath = explode('.', $path);

        $arrData = [];

        if (null === $fileContent || empty($arrPath)) {
            $arrData = $this->getDataFromPath(json_decode($fileContent), $arrPath);
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
}
