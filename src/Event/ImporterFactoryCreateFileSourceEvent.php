<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Event;

use Contao\Model;
use HeimrichHannot\EntityImportBundle\Source\FileSource;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\EventDispatcher\Event;

class ImporterFactoryCreateFileSourceEvent extends Event
{
    public const NAME = 'huh.entity_import.importer_factory_create_filesource_event';

    /**
     * @var FileSource
     */
    private $fileSource;
    /**
     * @var ModelUtil
     */
    private $modelUtil;
    /**
     * @var FileUtil
     */
    private $fileUtil;
    /**
     * @var Model
     */
    private $sourceModel;

    /**
     * ImporterFactoryCreateFileSourceEvent constructor.
     */
    public function __construct(Model $sourceModel, FileUtil $fileUtil, ModelUtil $modelUtil)
    {
        $this->fileUtil = $fileUtil;
        $this->modelUtil = $modelUtil;
        $this->sourceModel = $sourceModel;
    }

    public function getFileSource()
    {
        return $this->fileSource;
    }

    public function setFileSource(FileSource $fileSource)
    {
        $this->fileSource = $fileSource;
    }
}
