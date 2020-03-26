<?php

/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['system']['entityImport'] = [
    'tables' => [HeimrichHannot\EntityImportBundle\Model\EntityImportSourceModel::getTable(), HeimrichHannot\EntityImportBundle\Model\EntityImportConfigModel::getTable()],
    'icon'   => '',
    'import' => [HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer::class, 'import'],
    'dryRun' => [HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer::class, 'dryRun']
];

/**
 * Models
 */
$GLOBALS['TL_MODELS'][HeimrichHannot\EntityImportBundle\Model\EntityImportSourceModel::getTable()] = 'HeimrichHannot\EntityImportBundle\Model\EntityImportSourceModel';
$GLOBALS['TL_MODELS'][HeimrichHannot\EntityImportBundle\Model\EntityImportConfigModel::getTable()] = 'HeimrichHannot\EntityImportBundle\Model\EntityImportConfigModel';