<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

use Contao\StringUtil;
use HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer;
use HeimrichHannot\EntityImportBundle\Event\ImporterFactoryCreateFileSourceEvent;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Request\CurlRequestUtil;
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
     * @var CurlRequestUtil
     */
    private $curlRequestUtil;

    public function __construct(ModelUtil $modelUtil, FileUtil $fileUtil, EventDispatcher $eventDispatcher, CurlRequestUtil $curlRequestUtil)
    {
        $this->modelUtil = $modelUtil;
        $this->fileUtil = $fileUtil;
        $this->eventDispatcher = $eventDispatcher;
        $this->curlRequestUtil = $curlRequestUtil;
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
                        $source = new JSONFileSource($this->fileUtil, $this->modelUtil, $this->curlRequestUtil);

                        break;

                    case EntityImportSourceContainer::FILETYPE_CSV:
                        $source = new CSVFileSource($this->fileUtil, $this->modelUtil, $this->curlRequestUtil);

                        break;

                    default:
                        $event = $this->eventDispatcher->dispatch(ImporterFactoryCreateFileSourceEvent::NAME, new ImporterFactoryCreateFileSourceEvent($this->fileUtil, $this->modelUtil));
                        $source = $event->getFileSource();

                        if (null === $source) {
                            throw new \Exception('No file source class found for file type '.$sourceModel->fileType);
                        }

                        break;
                }

                break;
        }

        $source->setFieldMapping(StringUtil::deserialize($sourceModel->fieldMapping, true));
        $source->setFilePath($sourceModel->id);
        $source->setSourceModel($sourceModel);

        return $source;
    }
}
