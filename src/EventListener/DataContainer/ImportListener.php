<?php
/**
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\EventListener\DataContainer;

use Contao\Backend;

class ImportListener extends Backend
{

    protected $activeBundles;

    public function __construct(array $activeBundles)
    {
        $this->activeBundles = $activeBundles;
        parent::__construct();
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

        if ($row['start'] || $row['end'])
        {
            $label .= '&nbsp;<strong>[';

            if ($row['start'])
            {
                $label .= $GLOBALS['TL_LANG']['tl_entity_import']['start'][0] . ': ' . \Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $row['start']);

                if ($row['end'])
                {
                    $label .= '&nbsp;-&nbsp;';
                }
            }

            if ($row['end'])
            {
                $label .= $GLOBALS['TL_LANG']['tl_entity_import']['end'][0] . ': ' . \Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $row['end']);
            }

            $label .= ']</strong>';
        }

        return $label;
    }

}