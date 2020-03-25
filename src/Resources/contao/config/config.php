<?php

/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['system']['entityImport'] = [
    'tables' => [HeimrichHannot\EntityImportBundle\Model\EntityImportSourceModel::getTable(), \HeimrichHannot\EntityImportBundle\Model\EntityImportHandlerModel::getTable()],
    'icon'   => '',
];



/**
 * Models
 */
$GLOBALS['TL_MODELS'][HeimrichHannot\EntityImportBundle\Model\EntityImportSourceModel::getTable()] = 'HeimrichHannot\EntityImportBundle\Model\EntityImportSourceModel';
$GLOBALS['TL_MODELS'][HeimrichHannot\EntityImportBundle\Model\EntityImportHandlerModel::getTable()] = 'HeimrichHannot\EntityImportBundle\Model\EntityImportHandlerModel';