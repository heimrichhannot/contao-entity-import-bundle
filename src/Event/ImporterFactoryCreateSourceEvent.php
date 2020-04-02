<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Event;

use HeimrichHannot\EntityImportBundle\Source\Source;
use HeimrichHannot\EntityImportBundle\Source\SourceInterface;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\EventDispatcher\Event;

class ImporterFactoryCreateSourceEvent extends Event
{
    public const NAME = 'huh.entity_import.importer_factory_create_source_event';

    /**
     * @var SourceInterface
     */
    private $source;
    /**
     * @var ModelUtil
     */
    private $modelUtil;
    /**
     * @var FileUtil
     */
    private $fileUtil;

    public function __construct(SourceInterface $source, FileUtil $fileUtil, ModelUtil $modelUtil)
    {
        $this->fileUtil = $fileUtil;
        $this->modelUtil = $modelUtil;
        $this->source = $source;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function setSource(Source $source)
    {
        $this->source = $source;
    }
}
