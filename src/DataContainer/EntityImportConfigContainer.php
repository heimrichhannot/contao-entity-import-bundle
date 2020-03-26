<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\DataContainer;

use Contao\Config;
use Contao\Database;
use Contao\Date;
use Contao\System;
use HeimrichHannot\EntityImportBundle\Importer\Importer;
use HeimrichHannot\EntityImportBundle\Model\EntityImportConfigModel;
use HeimrichHannot\EntityImportBundle\Model\EntityImportSourceModel;
use HeimrichHannot\EntityImportBundle\Source\JSONFileSource;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\EventDispatcher\EventDispatcher;

class EntityImportConfigContainer
{
    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getAllTargetTables($dc)
    {
        return array_values(Database::getInstance()->listTables(null, true));
    }

    public function getSourceFields($dc)
    {
        $arrOptions = [];

        $fieldMapping = EntityImportSourceModel::findByPk($dc->id)->fieldMapping;

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
//        $this->request->getGet('id') -> config -> pid -> source
//        new JSONFileSource()
//        init()
//            run()

        $sourceId = Database::getInstance()->getParentRecords($this->request->getGet('id'), $this->request->getGet('table'));

        $source = EntityImportSourceModel::findOneBy('id', $sourceId);

        switch ($source->fileType) {
            case EntityImportSourceContainer::FILETYPE_JSON:
                $concreteSource = new JSONFileSource($source);
                break;
            case EntityImportSourceContainer::FILETYPE_CSV:

                break;
            default:
                break;
        }

        $importer = new Importer(new EventDispatcher(), new DatabaseUtil(System::getContainer()->get('contao.framework')));

        $config = EntityImportConfigModel::findOneBy('id', $this->request->getGet('id'));

        if (null === $config) {
            new Exception('SourceModel not defined');
        }

        $importer->init($concreteSource, $config);
        $importer->run();

        return true;
    }

    public function listItems($row)
    {
        return '<div class="tl_content_left">'.$row['title'].' <span style="color:#999;padding-left:3px">['.Date::parse(Config::get('datimFormat'), $row['date']).']</span></div>';
    }
}
