<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\DataContainer;

use Contao\Input;
use HeimrichHannot\EntityImportBundle\Model\EntityImportConfigModel;

class EntityImportConfigContainer
{
    public static function initPalette()
    {
        $objEntityImportConfig = EntityImportConfigModel::findByPk(Input::get('id'));
        $arrDca = &$GLOBALS['TL_DCA']['tl_entity_import_config'];
        $strParentType = EntityImportConfigModel::findByPk($objEntityImportConfig->pid)->type;

        // add default palettes
        $arrDca['palettes']['default'] .= $arrDca['typepalettes'][$strParentType];

        switch ($strParentType) {
            case \HeimrichHannot\EntityImportBundle\DataContainer\EntityImportContainer::TYPE_DATABASE:
                break;
            default:
                $arrDca['fields']['mergeIdentifierFields']['eval']['multiColumnEditor']['fields']['source']['inputType'] = 'text';
                break;
        }

        // HOOK: add custom logic
        if (isset($GLOBALS['TL_HOOKS']['initEntityImportPalettes']) && \is_array($GLOBALS['TL_HOOKS']['initEntityImportPalettes'])) {
            foreach ($GLOBALS['TL_HOOKS']['initEntityImportPalettes'] as $arrCallback) {
                if (null !== ($objCallback = \Controller::importStatic($arrCallback[0]))) {
                    $objCallback->{$arrCallback[1]}($objEntityImportConfig, $arrDca);
                }
            }
        }
    }

    public static function initNewsPalette($objEntityImportConfig, &$arrDca)
    {
        switch ($objEntityImportConfig->dbTargetTable) {
            case 'tl_news':
                $arrDca['palettes']['default'] .= '{category_legend},catContao';
                break;
        }
    }

    public static function getImporterClasses()
    {
        $arrOptions = [];

        $classes = $GLOBALS['ENTITY_IMPORTER'];

        foreach ($classes as $strClass => $strName) {
            if (!@class_exists($strClass)) {
                continue;
            }

            $arrOptions[$strClass] = $strName;
        }

        asort($arrOptions);

        return $arrOptions;
    }

    public function getSourceFields($dc)
    {
        $arrOptions = [];

        $objModel = EntityImportConfigModel::findByPk($dc->id);

        if (null === $objModel || null === $objModel->dbSourceTable) {
            return $arrOptions;
        }

        $arrFields = \HeimrichHannot\EntityImport\Database::getInstance(
            \HeimrichHannot\EntityImport\EntityImportModel::findByPk($objModel->pid)->row()
        )->listFields($objModel->dbSourceTable);

        if (!\is_array($arrFields) || empty($arrFields)) {
            return $arrOptions;
        }

        foreach ($arrFields as $arrField) {
            if (\in_array($arrField['type'], ['index'], true)) {
                continue;
            }

            $arrOptions[$arrField['name']] = $arrField['name'].' ['.$arrField['origtype'].']';
        }

        return $arrOptions;
    }

    public function getTargetFields($dc)
    {
        $arrOptions = [];

        $objModel = EntityImportConfigModel::findByPk($dc->id);

        if (null === $objModel || !$objModel->dbTargetTable) {
            return $arrOptions;
        }

        $arrFields = \Database::getInstance()->listFields($objModel->dbTargetTable);

        if (!\is_array($arrFields) || empty($arrFields)) {
            return $arrOptions;
        }

        $arrOptions['tl_content'] = &$GLOBALS['TL_LANG']['tl_entity_import_config']['createNewContentElement'];

        foreach ($arrFields as $arrField) {
            if (\in_array($arrField, ['index'], true)) {
                continue;
            }

            $arrOptions[$arrField['name']] = $arrField['name'].' ['.$arrField['origtype'].']';
        }

        return $arrOptions;
    }

    public function getMergeTargetFields($dc)
    {
        $arrOptions = $this->getTargetFields($dc);

        unset($arrOptions['tl_content']);

        return $arrOptions;
    }

    public function getTargetFileFields($dc)
    {
        $arrOptions = [];

        $arrFields = \Database::getInstance()->listFields('tl_files');

        if (!\is_array($arrFields) || empty($arrFields)) {
            return $arrOptions;
        }

        foreach ($arrFields as $arrField) {
            if (\in_array($arrField['type'], ['index'], true)) {
                continue;
            }

            $arrOptions[$arrField['name']] = $arrField['name'].' ['.$arrField['origtype'].']';
        }

        return $arrOptions;
    }

    public function getSourceTables(\DataContainer $dc)
    {
        if (null === ($source = \HeimrichHannot\EntityImportBundle\Model\EntityImportModel::findByPk($dc->activeRecord->pid))) {
            return [];
        }

        $arrTables = HeimrichHannot\EntityImportBundle\Importer\DatabaseImporter::getInstance(
            $source->row()
        )->listTables();

        return array_values($arrTables);
    }

    public function getTargetTables(\DataContainer $dc)
    {
        $arrTables = \HeimrichHannot\EntityImport\Database::getInstance()->listTables();

        return array_values($arrTables);
    }

    public function getContaoCategories(DataContainer $dc)
    {
        $arrOptions = [];

        if (!\in_array('news_categories', \Config::getInstance()->getActiveModules(), true)) {
            return $arrOptions;
        }

        $objCategories = \NewsCategories\NewsCategoryModel::findBy('published', 1);

        if (null === $objCategories) {
            return $arrOptions;
        }

        while ($objCategories->next()) {
            $arrOptions[$objCategories->id] = $objCategories->title;
        }

        return $arrOptions;
    }

    public function getTypoCategories(DataContainer $dc)
    {
        $arrOptions = [];

        if (!\in_array('news_categories', \Config::getInstance()->getActiveModules(), true)) {
            return $arrOptions;
        }

        $objCategories = \HeimrichHannot\EntityImport\Database::getInstance()->prepare('SELECT * FROM tt_news_cat WHERE deleted = 0 AND hidden=0')->execute();

        if ($objCategories->count() < 1) {
            return $arrOptions;
        }

        while ($objCategories->next()) {
            $arrOptions[$objCategories->uid] = $objCategories->title;
        }

        return $arrOptions;
    }

    public function getPidsFromTable(DataContainer $dc)
    {
        $arrArchives = [];

        $objArchives = \HeimrichHannot\Typort\Database::getInstance()->prepare(
            'SELECT p.title, p.uid, COUNT(n.uid) AS total FROM '.$dc->activeRecord->type.' n
			INNER JOIN pages p ON p.uid = n.pid
			WHERE n.deleted=0 AND p.deleted = 0 GROUP BY n.pid ORDER BY n.pid'
        )->execute();

        if (null === $objArchives) {
            return $arrArchives;
        }

        while ($objArchives->next()) {
            $arrArchives[$objArchives->uid] = $objArchives->title.' [Id: '.$objArchives->uid.'] (Count:'.$objArchives->total.')';
        }

        return $arrArchives;
    }

    public function listEntityImportConfig($arrRow)
    {
        $strText = $arrRow['description'] ? '<span style="color:#b3b3b3;padding-left:3px"> ['.$arrRow['description'].'] </span>' : '';

        return '<div class="tl_content_left">'.$arrRow['title'].$strText.'</div>';
    }

    public function getExternalImporterClasses()
    {
        $arrOptions = [];

        $classes = $GLOBALS['EXTERNAL_ENTITY_IMPORTER'];

        foreach ($classes as $strClass => $strName) {
            if (!@class_exists($strClass)) {
                continue;
            }

            $arrOptions[$strClass] = $strName;
        }

        asort($arrOptions);

        return $arrOptions;
    }
}
