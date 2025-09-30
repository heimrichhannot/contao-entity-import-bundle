<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Controller;

use HeimrichHannot\EntityImportBundle\Importer\ImporterFactory;
use HeimrichHannot\EntityImportBundle\Importer\ImporterInterface;
use HeimrichHannot\UtilsBundle\Util\Utils;

class PoorManCronController
{
    protected ImporterFactory $importerFactory;
    protected Utils           $utils;

    public function __construct(ImporterFactory $importerFactory, Utils $utils)
    {
        $this->importerFactory = $importerFactory;
        $this->utils = $utils;
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
        $models = $this->utils->model()->findModelInstancesBy('tl_entity_import_config',
            ['tl_entity_import_config.useCron=?', 'tl_entity_import_config.cronInterval=?', 'tl_entity_import_config.usePoorMansCron=?'], [true, $interval, true]);

        if (null === $models) {
            return [];
        }

        return $models->fetchEach('id');
    }

    protected function run(string $id)
    {
        if (null === ($configModel = $this->utils->model()->findModelInstanceByPk('tl_entity_import_config', $id))) {
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
