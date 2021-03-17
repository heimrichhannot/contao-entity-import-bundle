<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Importer;

use HeimrichHannot\EntityImportBundle\Source\SourceFactory;
use HeimrichHannot\EntityImportBundle\Source\SourceInterface;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\String\StringUtil;
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
     * @var StringUtil
     */
    private $stringUtil;
    /**
     * @var DcaUtil
     */
    private $dcaUtil;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var ContainerUtil
     */
    private $containerUtil;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var FileUtil
     */
    private $fileUtil;

    /**
     * Importer constructor.
     */
    public function __construct(
        ContainerInterface $container,
        DatabaseUtil $databaseUtil,
        EventDispatcherInterface $eventDispatcher,
        Request $request,
        ModelUtil $modelUtil,
        StringUtil $stringUtil,
        DcaUtil $dcaUtil,
        SourceFactory $sourceFactory,
        ContainerUtil $containerUtil,
        FileUtil $fileUtil
    ) {
        $this->databaseUtil = $databaseUtil;
        $this->eventDispatcher = $eventDispatcher;
        $this->modelUtil = $modelUtil;
        $this->sourceFactory = $sourceFactory;
        $this->stringUtil = $stringUtil;
        $this->dcaUtil = $dcaUtil;
        $this->container = $container;
        $this->containerUtil = $containerUtil;
        $this->request = $request;
        $this->fileUtil = $fileUtil;
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
            $configModel,
            $source,
            $this->eventDispatcher,
            $this->request,
            $this->databaseUtil,
            $this->modelUtil,
            $this->stringUtil,
            $this->dcaUtil,
            $this->containerUtil,
            $this->fileUtil
        );
    }
}
