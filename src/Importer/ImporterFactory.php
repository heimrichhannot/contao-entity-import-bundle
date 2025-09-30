<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Importer;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Doctrine\DBAL\Connection;
use HeimrichHannot\EntityImportBundle\Source\SourceFactory;
use HeimrichHannot\EntityImportBundle\Source\SourceInterface;
use HeimrichHannot\EntityImportBundle\Util\EntityImportUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ImporterFactory
{
    protected EventDispatcherInterface $eventDispatcher;
    protected SourceFactory            $sourceFactory;
    protected Utils                    $utils;
    protected ContaoFramework          $framework;
    protected Connection               $connection;
    protected EntityImportUtil         $entityImportUtil;
    protected InsertTagParser          $insertTagParser;

    public function __construct(
        ContaoFramework $framework,
        EventDispatcherInterface $eventDispatcher,
        Connection $connection,
        EntityImportUtil $entityImportUtil,
        SourceFactory $sourceFactory,
        Utils $utils,
        InsertTagParser $insertTagParser
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->sourceFactory = $sourceFactory;
        $this->utils = $utils;
        $this->framework = $framework;
        $this->connection = $connection;
        $this->entityImportUtil = $entityImportUtil;
        $this->insertTagParser = $insertTagParser;
    }

    public function createInstance($configModel, array $options = []): ?ImporterInterface
    {
        $sourceModel = $options['sourceModel'] ?? null;

        if (is_numeric($configModel) && null === ($configModel = $this->utils->model()->findModelInstanceByPk('tl_entity_import_config', $configModel))) {
            return null;
        }

        if (null === $sourceModel && null === ($sourceModel = $this->utils->model()->findModelInstanceByPk('tl_entity_import_source', $configModel->pid))) {
            return null;
        }

        /**
         * @var SourceInterface
         */
        $source = $this->sourceFactory->createInstance($sourceModel->id);

        // set domain
        $source->setDomain($configModel->cronDomain);

        return new Importer(
            $this->framework,
            $configModel,
            $source,
            $this->eventDispatcher,
            $this->connection,
            $this->entityImportUtil,
            $this->utils,
            $this->insertTagParser
        );
    }
}
