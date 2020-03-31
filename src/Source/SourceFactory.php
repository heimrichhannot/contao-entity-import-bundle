<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

use HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer;
use HeimrichHannot\EntityImportBundle\Event\ImporterFactoryCreateFileSourceEvent;
use HeimrichHannot\EntityImportBundle\Event\ImporterFactoryCreateSourceEvent;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
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

    public function __construct(ModelUtil $modelUtil, FileUtil $fileUtil, EventDispatcher $eventDispatcher)
    {
        $this->modelUtil = $modelUtil;
        $this->fileUtil = $fileUtil;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function createInstance(int $sourceModel): ?SourceInterface
    {
        if (null === ($sourceModel = $this->modelUtil->findModelInstanceByPk('tl_entity_import_source', $sourceModel))) {
            return null;
        }

        $source = null;

        // TODO -> change to config.yml
        switch ($sourceModel->sourceType) {
            case EntityImportSourceContainer::SOURCE_TYPE_CONTAO_FILE_SYSTEM:
                switch ($sourceModel->fileType) {
                    case EntityImportSourceContainer::FILETYPE_JSON:
                        $source = new JSONFileSource($this->fileUtil, $this->modelUtil);
                        break;
                    case EntityImportSourceContainer::FILETYPE_CSV:
                        $source = new CSVFileSource($this->fileUtil, $this->modelUtil);
                        break;
                    default:
                        $event = $this->eventDispatcher->dispatch(ImporterFactoryCreateFileSourceEvent::NAME, new ImporterFactoryCreateFileSourceEvent($this->fileUtil, $this->modelUtil));
                        $source = $event->getFileSource();
                        break;
                }
                break;
//            case EntityImportSourceContainer::SOURCE_TYPE_ABSOLUTE_PATH:
//                break;
//            case EntityImportSourceContainer::SOURCE_TYPE_HTTP:
//                break;
            default:
                $event = $this->eventDispatcher->dispatch(ImporterFactoryCreateSourceEvent::NAME, new ImporterFactoryCreateSourceEvent($this->fileUtil, $this->modelUtil));
                $source = $event->getSource();
                break;
        }

        $source->setFieldMapping(unserialize($sourceModel->fieldMapping));
        $source->setFilePath($sourceModel->id);
        $source->setSourceModel($sourceModel);

        return $source;
    }
}
