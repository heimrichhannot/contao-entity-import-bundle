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
use HeimrichHannot\EntityImportBundle\Util\EntityImportUtil;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\CoreBundle\Slug\Slug;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\DBAL\Connection;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ImporterFactory
{
    public function __construct(
        protected ParameterBagInterface $parameterBag,
        protected ContaoFramework $framework,
        protected EventDispatcherInterface $eventDispatcher,
        protected RequestStack $requestStack,
        protected Connection $conn,
        protected SourceFactory $sourceFactory,
        protected Utils $utils,
        protected EntityImportUtil $util,
        protected Slug $slug,
        protected HttpClientInterface $httpClient,
        protected InsertTagParser $insertTagParser
    ) {
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
            $this->parameterBag,
            $this->framework,
            $configModel,
            $source,
            $this->eventDispatcher,
            $this->requestStack,
            $this->conn,
            $this->util,
            $this->slug,
            $this->httpClient,
            $this->insertTagParser
        );
    }
}
