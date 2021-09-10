<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Importer;

use Contao\CoreBundle\Framework\ContaoFramework;
use HeimrichHannot\EntityImportBundle\Source\SourceFactory;
use HeimrichHannot\EntityImportBundle\Source\SourceInterface;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ImporterFactory
{
    /**
     * @var DatabaseUtil
     */
    private $databaseUtil;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ModelUtil
     */
    private $modelUtil;

    /**
     * @var SourceFactory
     */
    private $sourceFactory;
    /**
     * @var DcaUtil
     */
    private $dcaUtil;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var FileUtil
     */
    private $fileUtil;
    /**
     * @var Utils
     */
    private $utils;

    /**
     * @var ContaoFramework
     */
    private $framework;

    /**
     * Importer constructor.
     */
    public function __construct(
        ContainerInterface $container,
        ContaoFramework $framework,
        DatabaseUtil $databaseUtil,
        EventDispatcherInterface $eventDispatcher,
        Request $request,
        ModelUtil $modelUtil,
        DcaUtil $dcaUtil,
        SourceFactory $sourceFactory,
        FileUtil $fileUtil,
        Utils $utils
    ) {
        $this->databaseUtil = $databaseUtil;
        $this->eventDispatcher = $eventDispatcher;
        $this->modelUtil = $modelUtil;
        $this->sourceFactory = $sourceFactory;
        $this->dcaUtil = $dcaUtil;
        $this->container = $container;
        $this->request = $request;
        $this->fileUtil = $fileUtil;
        $this->utils = $utils;
        $this->framework = $framework;
    }

    public function createInstance($configModel, array $options = []): ?ImporterInterface
    {
        $sourceModel = $options['sourceModel'] ?? null;

        if (is_numeric($configModel) && null === ($configModel = $this->modelUtil->findModelInstanceByPk('tl_entity_import_config', $configModel))) {
            return null;
        }

        if (null === $sourceModel && null === ($sourceModel = $this->modelUtil->findModelInstanceByPk('tl_entity_import_source', $configModel->pid))) {
            return null;
        }

        /**
         * @var SourceInterface
         */
        $source = $this->sourceFactory->createInstance($sourceModel->id);

        // set domain
        $source->setDomain($configModel->cronDomain);

        return new Importer(
            $this->container,
            $this->framework,
            $configModel,
            $source,
            $this->eventDispatcher,
            $this->request,
            $this->databaseUtil,
            $this->dcaUtil,
            $this->fileUtil,
            $this->utils
        );
    }
}
