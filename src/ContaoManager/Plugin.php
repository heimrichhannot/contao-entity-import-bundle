<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Config\ConfigPluginInterface;
use HeimrichHannot\EntityImportBundle\HeimrichHannotEntityImportBundle;
use Symfony\Component\Config\Loader\LoaderInterface;

class Plugin implements BundlePluginInterface, ConfigPluginInterface
{
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(HeimrichHannotEntityImportBundle::class)
                ->setLoadAfter([
                    ContaoCoreBundle::class,
                ]),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader, array $managerConfig)
    {
        $loader->load('@HeimrichHannotEntityImportBundle/Resources/config/datacontainers.yml');
        $loader->load('@HeimrichHannotEntityImportBundle/Resources/config/commands.yml');
        $loader->load('@HeimrichHannotEntityImportBundle/Resources/config/controllers.yml');
        $loader->load('@HeimrichHannotEntityImportBundle/Resources/config/services.yml');
        $loader->load('@HeimrichHannotEntityImportBundle/Resources/config/listeners.yml');
    }
}
