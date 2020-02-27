<?php

/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['system']['entityImport'] = [
    'tables' => ['tl_entity_import'],
    'icon'   => '',
];



/**
 * Models
 */
//$GLOBALS['TL_MODELS'][HeimrichHannot\EntityImportBundle\Model\EntityImportConfigModel::getTable()] = 'HeimrichHannot\EntityImportBundle\Model\EntityImportConfigModel';
$GLOBALS['TL_MODELS'][HeimrichHannot\EntityImportBundle\Model\EntityImportModel::getTable()] = 'HeimrichHannot\EntityImportBundle\Model\EntityImportModel';