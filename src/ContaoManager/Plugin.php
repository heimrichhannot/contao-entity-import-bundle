<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\EntityImportBundle\ContaoManager;


use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use HeimrichHannot\EntityImportBundle\ContaoEntityImportBundle;

class Plugin implements BundlePluginInterface
{

    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(ContaoEntityImportBundle::class)
                ->setLoadAfter([
                    ContaoCoreBundle::class
                ])
        ];
    }
}