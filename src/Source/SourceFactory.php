<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

use HeimrichHannot\EntityImportBundle\EventListener\DataContainer\EntityImportSourceContainer;
use HeimrichHannot\EntityImportBundle\Event\SourceFactoryCreateSourceEvent;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\CoreBundle\Framework\ContaoFramework;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SourceFactory
{
    /**
     * SourceFactory constructor.
     */
    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
        protected Utils $utils,
        protected ParameterBagInterface $parameterBag,
        protected InsertTagParser $insertTagParser,
        protected ContaoFramework $framework
    )
    {
    }

    public function createInstance(int $sourceModel): ?SourceInterface
    {
        if (null === ($sourceModel = $this->utils->model()->findModelInstanceByPk('tl_entity_import_source', $sourceModel))) {
            return null;
        }

        $source = null;

        switch ($sourceModel->type) {
            case EntityImportSourceContainer::TYPE_DATABASE:
                $source = new DatabaseSource($this->utils, $this->parameterBag, $this->insertTagParser, $this->framework);

                break;

            case EntityImportSourceContainer::TYPE_FILE:
                switch ($sourceModel->fileType) {
                    case EntityImportSourceContainer::FILETYPE_JSON:
                        $source = new JSONFileSource($this->eventDispatcher, $this->utils, $this->parameterBag, $this->insertTagParser);

                        break;

                    case EntityImportSourceContainer::FILETYPE_XML:
                        $source = new XmlFileSource($this->eventDispatcher, $this->utils, $this->parameterBag, $this->insertTagParser);

                        break;

                    case EntityImportSourceContainer::FILETYPE_CSV:
                        $source = new CSVFileSource($this->eventDispatcher, $this->utils, $this->parameterBag, $this->insertTagParser);

                        break;

                    case EntityImportSourceContainer::FILETYPE_RSS:
                        $source = new RSSFileSource($this->eventDispatcher, $this->utils, $this->parameterBag, $this->insertTagParser);

                        break;


                }

                break;
            case EntityImportSourceContainer::TYPE_YOUTUBE:
                $source = new YouTubeSource($this->eventDispatcher, $this->utils, $this->parameterBag, $this->insertTagParser);
                break;
            case EntityImportSourceContainer::TYPE_INSTAGRAM:
                $source = new InstagramSource($this->eventDispatcher, $this->utils, $this->parameterBag, $this->insertTagParser);
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
