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
     * @var ModelUtil
     */
    private $modelUtil;

    /**
     * @var DatabaseUtil
     */
    private $databaseUtil;

    /**
     * @var SourceFactory
     */
    private $sourceFactory;
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * Importer constructor.
     */
    public function __construct(DatabaseUtil $databaseUtil, EventDispatcher $eventDispatcher, ModelUtil $modelUtil, SourceFactory $sourceFactory)
    {
        $this->databaseUtil = $databaseUtil;
        $this->sourceFactory = $sourceFactory;
        $this->modelUtil = $modelUtil;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function createInstance(int $sourceModel): ?ImporterInterface
    {
        if (null === ($configModel = $this->modelUtil->findModelInstanceByPk('tl_entity_import_config', $sourceModel))) {
            return null;
        }

        $importer = new Importer($this->eventDispatcher, $this->databaseUtil, $this->modelUtil);

        $importer->init($configModel->id, $sourceModel);

        return $importer;
    }
}
