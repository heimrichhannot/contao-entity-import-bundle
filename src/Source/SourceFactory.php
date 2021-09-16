<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

use HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer;
use HeimrichHannot\EntityImportBundle\Event\SourceFactoryCreateSourceEvent;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\String\StringUtil;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SourceFactory
{
    protected ModelUtil $modelUtil;
    protected FileUtil $fileUtil;
    protected EventDispatcherInterface $eventDispatcher;
    protected StringUtil $stringUtil;
    protected ContainerUtil $containerUtil;
    protected DcaUtil $dcaUtil;

    /**
     * SourceFactory constructor.
     */
    public function __construct(ModelUtil $modelUtil, FileUtil $fileUtil, EventDispatcherInterface $eventDispatcher, StringUtil $stringUtil, ContainerUtil $containerUtil, DcaUtil $dcaUtil)
    {
        $this->modelUtil = $modelUtil;
        $this->fileUtil = $fileUtil;
        $this->eventDispatcher = $eventDispatcher;
        $this->stringUtil = $stringUtil;
        $this->containerUtil = $containerUtil;
        $this->dcaUtil = $dcaUtil;
    }

    public function createInstance(int $sourceModel): ?SourceInterface
    {
        if (null === ($sourceModel = $this->modelUtil->findModelInstanceByPk('tl_entity_import_source', $sourceModel))) {
            return null;
        }

        $source = null;

        switch ($sourceModel->type) {
            case EntityImportSourceContainer::TYPE_DATABASE:
                $source = new DatabaseSource($this->dcaUtil);

                break;

            case EntityImportSourceContainer::TYPE_FILE:
                switch ($sourceModel->fileType) {
                    case EntityImportSourceContainer::FILETYPE_JSON:
                        $source = new JSONFileSource($this->eventDispatcher, $this->fileUtil, $this->stringUtil, $this->containerUtil);

                        break;

                    case EntityImportSourceContainer::FILETYPE_CSV:
                        $source = new CSVFileSource($this->eventDispatcher, $this->fileUtil, $this->stringUtil, $this->containerUtil);

                        break;

                    case EntityImportSourceContainer::FILETYPE_RSS:
                        $source = new RSSFileSource($this->eventDispatcher, $this->fileUtil, $this->stringUtil, $this->containerUtil);

                        break;
                }

                break;
        }

        $event = $this->eventDispatcher->dispatch(new SourceFactoryCreateSourceEvent(
            $source,
            $sourceModel
        ), SourceFactoryCreateSourceEvent::NAME);

        $source = $event->getSource();

        if (null === $source) {
            throw new \Exception('No file source class found for file type '.$sourceModel->fileType);
        }

        $source->setFieldMapping(\Contao\StringUtil::deserialize($sourceModel->fieldMapping, true));
        $source->setSourceModel($sourceModel);

        return $source;
    }
}
