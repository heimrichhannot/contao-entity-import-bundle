<?php

/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['system']['entityImport'] = [
    'tables' => ['tl_entity_import', 'tl_entity_import_config'],
    'icon'   => '',
];



/**
 * Models
 */
$GLOBALS['TL_MODELS'][HeimrichHannot\EntityImportBundle\Model\EntityImportConfigModel::getTable()] = 'HeimrichHannot\EntityImportBundle\Model\EntityImportConfigModel';