<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use HeimrichHannot\EntityImportBundle\Importer\ImporterFactory;
use HeimrichHannot\EntityImportBundle\Importer\ImporterInterface;
use HeimrichHannot\UtilsBundle\Arrays\ArrayUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class PoorManCronController
{
    /**
     * @var ContaoFramework
     */
    private $framework;
    /**
     * @var ArrayUtil
     */
    private $arrayUtil;
    /**
     * @var ImporterFactory
     */
    private $importerFactory;
    /**
     * @var ModelUtil
     */
    private $modelUtil;

    public function __construct(ContaoFramework $framework, ImporterFactory $importerFactory, ArrayUtil $arrayUtil, ModelUtil $modelUtil)
    {
        $this->framework = $framework;
        $this->arrayUtil = $arrayUtil;
        $this->importerFactory = $importerFactory;
        $this->modelUtil = $modelUtil;
    }

    public function runMinutely()
    {
        $items = $this->getConfigIds('minutely');

        foreach ($items as $item) {
            $this->run($item);
        }
    }

    public function runHourly()
    {
        $items = $this->getConfigIds('hourly');

        foreach ($items as $item) {
            $this->run($item);
        }
    }

    public function runDaily()
    {
        $items = $this->getConfigIds('daily');

        foreach ($items as $item) {
            $this->run($item);
        }
    }

    public function runWeekly()
    {
        $items = $this->getConfigIds('weekly');

        foreach ($items as $item) {
            $this->run($item);
        }
    }

    public function runMonthly()
    {
        $items = $this->getConfigIds('monthly');

        foreach ($items as $item) {
            $this->run($item);
        }
    }

    protected function getConfigIds(string $interval): array
    {
        $models = $this->modelUtil->findModelInstancesBy('tl_entity_import_config',
            ['tl_entity_import_config.useCron=?', 'tl_entity_import_config.cronInterval=?'], [true, $interval]);

        if (null === $models) {
            return [];
        }

        return $models->fetchEach('id');
    }

    protected function run(string $id)
    {
        if (null === ($configModel = $this->modelUtil->findModelInstanceByPk('tl_entity_import_config', $id))) {
            return;
        }

        if ($configModel->cronLanguage) {
            $language = $GLOBALS['TL_LANGUAGE'];

            $GLOBALS['TL_LANGUAGE'] = $configModel->cronLanguage;
        }

        /** @var ImporterInterface $importer */
        $importer = $this->importerFactory->createInstance($configModel->id);
        $importer->run();

        if ($configModel->cronLanguage) {
            $GLOBALS['TL_LANGUAGE'] = $language;
        }
    }
}
