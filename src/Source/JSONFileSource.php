<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

class JSONFileSource extends FileSource
{
    public function getData(): array
    {
        $data = [];
        $fileContent = file_get_contents($this->filePath);

        if (null !== $fileContent) {
            $data = json_decode($fileContent);
        }

        return $data;
    }
}
