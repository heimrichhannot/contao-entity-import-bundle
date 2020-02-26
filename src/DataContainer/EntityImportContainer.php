<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\DataContainer;

use Contao\Backend;
use Contao\Database;

class EntityImportContainer extends Backend
{
    const TYPE_DATABASE = 'db';
    const TYPE_FILE = 'file';

    const SOURCE_TYPE_HTTP = 'http';
    const SOURCE_TYPE_CONTAO_FILE_SYSTEM = 'contao_file_system';
    const SOURCE_TYPE_ABSOLUTE_PATH = 'absolute_path';

    const FILETYPE_CSV = 'csv';
    const FILETYPE_JSON = 'json';

    protected $activeBundles;
    protected $database;

    public function __construct()
    {
        $this->activeBundles = $this->getContainer()->getParameter('kernel.bundles');
        $this->database = Database::getInstance();
        parent::__construct();
    }

    public function onOptionsFileSRC($dc)
    {
        $file = \FilesModel::findByUuid($dc->value);

        if ($file) {
            $this->processInputFile($file, $dc->id);
        }

        return $dc;
    }

    public function onSaveFileSRC($value, $dc)
    {
        return $value;
    }

    public function onLoadFileSRC($value, $dc)
    {
        $file1 = \FilesModel::findByUuid($value);

        return $value;
    }

    public function onLoadJSONFileContent($value, $dc)
    {
        return json_encode(unserialize($value), JSON_PRETTY_PRINT);
    }

//    public function getContaoCategories(DataContainer $dc)
//    {
//        $arrOptions = [];
//
//        if (!in_array('news_categories', $this->activeBundles))
//        {
//            return $arrOptions;
//        }
//
//        $objCategories = NewsCategories\NewsCategoryModel::findBy('published', 1);
//
//
//
//        if ($objCategories === null)
//        {
//            return $arrOptions;
//        }
//
//        while ($objCategories->next())
//        {
//            $arrOptions[$objCategories->id] = $objCategories->title;
//        }
//
//        return $arrOptions;
//    }
//
//    public function getTypoCategories(DataContainer $dc)
//    {
//        $arrOptions = [];
//
//        if (!in_array('news_categories', \Config::getInstance()->getActiveModules()))
//        {
//            return $arrOptions;
//        }
//
//        $objCategories = HeimrichHannot\Typort\Database::getInstance()->prepare('SELECT * FROM tt_news_cat WHERE deleted = 0 AND hidden=0')->execute();
//
//        if ($objCategories->count() < 1)
//        {
//            return $arrOptions;
//        }
//
//        while ($objCategories->next())
//        {
//            $arrOptions[$objCategories->uid] = $objCategories->title;
//        }
//
//        return $arrOptions;
//    }
//
//    public function getPidsFromTable(DataContainer $dc)
//    {
//        $arrArchives = [];
//
//        $objArchives = HeimrichHannot\Typort\Database::getInstance()->prepare(
//            'SELECT p.title, p.uid, COUNT(n.uid) AS total FROM ' . $dc->activeRecord->type . ' n
    //			INNER JOIN pages p ON p.uid = n.pid
    //			WHERE n.deleted=0 AND p.deleted = 0 GROUP BY n.pid ORDER BY n.pid'
//        )->execute();
//
//        if ($objArchives === null)
//        {
//            return $arrArchives;
//        }
//
//        while ($objArchives->next())
//        {
//            $arrArchives[$objArchives->uid] = $objArchives->title . ' [Id: ' . $objArchives->uid . '] (Count:' . $objArchives->total . ')';
//        }
//
//        return $arrArchives;
//    }

    public function addDate($row, $label)
    {
        if ($row['start'] || $row['end']) {
            $label .= '&nbsp;<strong>[';

            if ($row['start']) {
                $label .= $GLOBALS['TL_LANG']['tl_entity_import']['start'][0].': '.\Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $row['start']);

                if ($row['end']) {
                    $label .= '&nbsp;-&nbsp;';
                }
            }

            if ($row['end']) {
                $label .= $GLOBALS['TL_LANG']['tl_entity_import']['end'][0].': '.\Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $row['end']);
            }

            $label .= ']</strong>';
        }

        return $label;
    }

    private function processInputFile($file, $id)
    {
        /* TODO: Restriktionen für Dateigrößen definieren */
        $fileSize = filesize($file->path);

        $fileContent = [];

        switch ($file->extension) {
            case static::FILETYPE_CSV:
                $csvFile = fopen($file->path, 'r');
                break;
            case static::FILETYPE_JSON:
                $jsonString = file_get_contents($file->path);
                $fileContent = json_decode($jsonString, true);
                break;
            default:

                break;
        }

        $blob = serialize($fileContent[0]);
        $this->database->prepare('UPDATE tl_entity_import SET JSONFileContent=? WHERE id=?')->execute($blob, $id);
    }
}
