<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

use HeimrichHannot\EntityImportBundle\Model\EntityImportSourceModel;

abstract class FileSource extends Source
{
    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var EntityImportSourceModel
     */
    protected $sourceModel;

    public function __construct(EntityImportSourceModel $sourceModel)
    {
        $this->sourceModel = $sourceModel;

        parent::__construct($this->sourceModel);

        $this->filePath = \FilesModel::findByUuid($this->sourceModel->fileSRC)->path;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }
}
