<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

abstract class FileSource implements SourceInterface
{
    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var array
     */
    protected $fileMapping;

    public function __construct(string $filePath, array $fileMapping)
    {
        $this->filePath = $filePath;
        $this->fileMapping = $fileMapping;
    }
}
