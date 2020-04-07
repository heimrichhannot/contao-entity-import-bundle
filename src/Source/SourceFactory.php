<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

use HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer;
use HeimrichHannot\EntityImportBundle\Event\SourceFactoryCreateSourceEvent;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\String\StringUtil;
use Symfony\Component\EventDispatcher\EventDispatcher;

class SourceFactory
{
    /**
     * @var ModelUtil
     */
    private $modelUtil;
    /**
     * @var FileUtil
     */
    private $fileUtil;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;
    /**
     * @var StringUtil
     */
    private $stringUtil;

    /**
     * SourceFactory constructor.
     */
    public function __construct(ModelUtil $modelUtil, FileUtil $fileUtil, EventDispatcher $eventDispatcher, StringUtil $stringUtil)
    {
        $this->modelUtil = $modelUtil;
        $this->fileUtil = $fileUtil;
        $this->eventDispatcher = $eventDispatcher;
        $this->stringUtil = $stringUtil;
    }

    public function createInstance(int $sourceModel): ?SourceInterface
    {
        if (null === ($sourceModel = $this->modelUtil->findModelInstanceByPk('tl_entity_import_source', $sourceModel))) {
            return null;
        }

        $source = null;

        // TODO -> change to config.yml
        switch ($sourceModel->type) {
            case EntityImportSourceContainer::TYPE_DATABASE:
                break;

            case EntityImportSourceContainer::TYPE_FILE:
                switch ($sourceModel->fileType) {
                    case EntityImportSourceContainer::FILETYPE_JSON:
                        $source = new JSONFileSource($this->fileUtil, $this->modelUtil, $this->stringUtil, $this->eventDispatcher);

                        break;

                    case EntityImportSourceContainer::FILETYPE_CSV:
                        $source = new CSVFileSource($this->fileUtil, $this->modelUtil, $this->stringUtil, $this->eventDispatcher);

                        break;
                }

                break;
        }

        $event = $this->eventDispatcher->dispatch(SourceFactoryCreateSourceEvent::NAME, new SourceFactoryCreateSourceEvent($source, $this->fileUtil, $this->modelUtil));
        $source = $event->getSource();

        if (null === $source) {
            throw new \Exception('No file source class found for file type '.$sourceModel->fileType);
        }

        $source->setFieldMapping(\Contao\StringUtil::deserialize($sourceModel->fieldMapping, true));
        $source->setSourceModel($sourceModel);

        return $source;
    }
}
