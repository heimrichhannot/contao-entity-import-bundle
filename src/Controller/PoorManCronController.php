<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Model;
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
     * @var Model
     */
    private $configModel;
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

    public function __construct(ContaoFramework $framework, Model $configModel, ArrayUtil $arrayUtil, ImporterFactory $importerFactory, ModelUtil $modelUtil)
    {
        $this->framework = $framework;
        $this->configModel = $configModel;
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

    protected function getConfigIds(string $interval)
    {
        $models = $this->modelUtil->findModelInstancesBy('tl_entity_import_config', ['tl_entity_import_config.useCron'], ['1'])->getModels();
        $result = [];

        foreach ($models as $model) {
            if ($model->cronInterval === $interval) {
                $result[] = $model->id;
            }
        }

        return $result;
    }

    protected function run(string $id)
    {
        if (null === ($configModel = $this->modelUtil->findModelInstanceByPk('tl_entity_import_config', $id))) {
            return;
        }

        if ($configModel->language) {
            $language = $GLOBALS['TL_LANGUAGE'];

            $GLOBALS['TL_LANGUAGE'] = $configModel->language;
        }

        /** @var ImporterInterface $importer */
        $importer = $this->importerFactory->createInstance($configModel->id);
        $importer->run();

        if ($configModel->language) {
            $GLOBALS['TL_LANGUAGE'] = $language;
        }
    }
}
