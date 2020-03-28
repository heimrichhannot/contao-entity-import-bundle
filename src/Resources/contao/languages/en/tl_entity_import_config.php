<?php

/**
 * Backend Modules
 */
$GLOBALS['TL_LANG']['tl_entity_import_config']['import'][0] = 'Import records';
$GLOBALS['TL_LANG']['tl_entity_import_config']['import'][1] = 'Import ID %s records';
$GLOBALS['TL_LANG']['tl_entity_import_config']['headline']  = 'Import records ID %s';
$GLOBALS['TL_LANG']['tl_entity_import_config']['label']     = 'Click &quot;Import records&quot; in order to start the import process.';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_entity_import_config']['new']    = ['New config', 'Create a new config'];
$GLOBALS['TL_LANG']['tl_entity_import_config']['show']   = ['Config details', 'Show config ID %s'];
$GLOBALS['TL_LANG']['tl_entity_import_config']['edit']   = ['Edit config', 'Edit config ID %s '];
$GLOBALS['TL_LANG']['tl_entity_import_config']['copy']   = ['Copy config', 'Copy config ID %s'];
$GLOBALS['TL_LANG']['tl_entity_import_config']['delete'] = ['Delete config', 'Delete config ID %s'];


/**
 * Misc
 */
$GLOBALS['TL_LANG']['tl_entity_import_config']['createNewContentElement'] = 'Create new tl_content entry';
$GLOBALS['TL_LANG']['tl_entity_import_config']['importConfirm']           = 'Are you sure You want to Import with this settings?';


/**
 * Messages
 */
$GLOBALS['TL_LANG']['tl_entity_import_config']['confirm']      = 'The records have been imported successfully.';
$GLOBALS['TL_LANG']['tl_entity_import_config']['importerInfo'] = 'Using the class "%s" for importing the records.';

/**
 * Errors
 */
$arrLang['error']['notInitialized']     = 'Importer is not yet initialized.';
$arrLang['error']['error']              = 'Error: %s';
$arrLang['error']['tableDoNotExist']    = 'Target table does not exist.';
$arrLang['error']['tableFieldsDiffer']  = 'Fields of target and source differ';
$arrLang['error']['noIdentifierFields'] = 'No unique identifier fields set.';
$arrLang['error']['successfulImport']   = 'Successfully imported %s records';
$arrLang['error']['emptyFile']          = 'Nothing to import';
$arrLang['error']['errorImport']        = 'Error inserted %s records. Error: %s';
$arrLang['error']['delimiter']          = 'Delimiter for csv is not defined';
$arrLang['error']['enclosure']          = 'Enclosure for csv is not defined';
$arrLang['error']['escape']             = 'Escape for csv is not defined';