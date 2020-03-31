<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\DataContainer;

use Contao\Config;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\Database;
use Contao\Date;
use HeimrichHannot\EntityImportBundle\Importer\ImporterFactory;
use HeimrichHannot\EntityImportBundle\Importer\ImporterInterface;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;

class EntityImportConfigContainer
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var ImporterInterface
     */
    private $importer;
    /**
     * @var UrlUtil
     */
    private $urlUtil;

    /**
     * @var ModelUtil
     */
    private $modelUtil;

    /**
     * @var ImporterFactory
     */
    private $importerFactory;

    /**
     * EntityImportConfigContainer constructor.
     */
    public function __construct(Request $request, ImporterFactory $importerFactory, UrlUtil $urlUtil, ModelUtil $modelUtil)
    {
        $this->request = $request;
        $this->urlUtil = $urlUtil;
        $this->modelUtil = $modelUtil;
        $this->importerFactory = $importerFactory;
    }

    public function getAllTargetTables($dc)
    {
        return array_values(Database::getInstance()->listTables(null, true));
    }

    public function getSourceFields($dc)
    {
        $arrOptions = [];

        $fieldMapping = $this->modelUtil->findModelInstanceByPk('tl_entity_import_source', $dc->id)->fieldMapping;

        $arrFieldMapping = unserialize($fieldMapping);

        if (!\is_array($arrFieldMapping) || empty($arrFieldMapping)) {
            return $arrOptions;
        }

        foreach ($arrFieldMapping as $arrField) {
            $arrOptions[$arrField['name']] = $arrField['name'].' ['.$arrField['value'].']';
        }

        return $arrOptions;
    }

    public function getTargetFields($dc)
    {
        $arrOptions = [];
        $arrFields = Database::getInstance()->listFields($dc->activeRecord->row()['targetTable']);

        if (!\is_array($arrFields) || empty($arrFields)) {
            return $arrOptions;
        }

        foreach ($arrFields as $arrField) {
            if (\in_array('index', $arrField, true)) {
                continue;
            }

            $arrOptions[$arrField['name']] = $arrField['name'].' ['.$arrField['origtype'].']';
        }

        return $arrOptions;
    }

    public function import()
    {
        $this->runImport(false);
    }

    public function dryRun()
    {
        $this->runImport(true);
    }

    public function listItems($row)
    {
        return '<div class="tl_content_left">'.$row['title'].' <span style="color:#999;padding-left:3px">['.Date::parse(Config::get('datimFormat'), $row['date']).']</span></div>';
    }

    private function runImport($dry = false)
    {
        $config = $this->request->getGet('id');

        if (null === ($configModel = $this->modelUtil->findModelInstanceByPk('tl_entity_import_config', $config))) {
            throw new \Exception(sprintf('Entity config model of ID %s not found', $config));
        }

        if (null === ($sourceModel = $this->modelUtil->findModelInstanceByPk('tl_entity_import_source', $configModel->pid))) {
            throw new \Exception(sprintf('Entity source model of ID %s not found', $configModel->pid));
        }

        $importer = $this->importerFactory->createInstance($sourceModel->id);
        $importer->setDryRun($dry);
        $importer->run();

        throw new RedirectResponseException($this->urlUtil->addQueryString('id='.$sourceModel->id, $this->urlUtil->removeQueryString(['key', 'id'])));
    }
}
