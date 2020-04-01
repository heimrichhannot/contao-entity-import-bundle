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
        if (null === ($sourceModel = $this->modelUtil->findModelInstanceByPk('tl_entity_import_source', $sourceModel))) {
            return null;
        }

        if (null === ($configModel = $this->modelUtil->findOneModelInstanceBy('tl_entity_import_config', ['tl_entity_import_config.pid'], [$sourceModel->id]))) {
            return null;
        }

        $source = $this->sourceFactory->createInstance($sourceModel->id);

        $importer = new Importer($this->eventDispatcher, $this->databaseUtil, $this->modelUtil);

        // TODO: remove; already contained in configmodel
        $this->targetTable = $this->configModel->targetTable;

        if (null === $this->configModel) {
            new \Exception('SourceModel not defined');
        }

        switch ($this->configModel->importSettings) {
            case 'mergeTable':
                $this->mergeTable = true;
                $this->purgeTableBeforeImport = false;
                break;
            case 'purgeTable':
                $this->mergeTable = false;
                $this->purgeTableBeforeImport = true;
                break;
            default:
                $this->mergeTable = false;
                $this->purgeTableBeforeImport = false;
                break;
        }

        return $importer;
    }
}
