<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Importer;

use HeimrichHannot\EntityImportBundle\Source\SourceFactory;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ImporterFactory
{
    /**
     * @var DatabaseUtil
     */
    private $databaseUtil;

    /**
     * @var EventDispatcher
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
     * Importer constructor.
     */
    public function __construct(DatabaseUtil $databaseUtil, EventDispatcher $eventDispatcher, ModelUtil $modelUtil, SourceFactory $sourceFactory)
    {
        $this->databaseUtil = $databaseUtil;
        $this->eventDispatcher = $eventDispatcher;
        $this->modelUtil = $modelUtil;
        $this->sourceFactory = $sourceFactory;
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

        return new Importer($configModel, $source, $this->eventDispatcher, $this->databaseUtil, $this->modelUtil);
    }
}
