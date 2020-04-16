<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Importer;

use HeimrichHannot\EntityImportBundle\Source\SourceFactory;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\String\StringUtil;
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
     * Importer constructor.
     */
    public function __construct(DatabaseUtil $databaseUtil, EventDispatcherInterface $eventDispatcher, ModelUtil $modelUtil, StringUtil $stringUtil, DcaUtil $dcaUtil, SourceFactory $sourceFactory)
    {
        $this->databaseUtil = $databaseUtil;
        $this->eventDispatcher = $eventDispatcher;
        $this->modelUtil = $modelUtil;
        $this->sourceFactory = $sourceFactory;
        $this->stringUtil = $stringUtil;
        $this->dcaUtil = $dcaUtil;
    }

    public function createInstance(int $configModel): ?ImporterInterface
    {
        if (null === ($configModel = $this->modelUtil->findModelInstanceByPk('tl_entity_import_config', $configModel))) {
            return null;
        }

        if (null === ($sourceModel = $this->modelUtil->findModelInstanceByPk('tl_entity_import_source', $configModel->pid))) {
            return null;
        }

        $source = $this->sourceFactory->createInstance($sourceModel->id);

        return new Importer($configModel, $source, $this->eventDispatcher, $this->databaseUtil, $this->modelUtil, $this->stringUtil, $this->dcaUtil);
    }
}
