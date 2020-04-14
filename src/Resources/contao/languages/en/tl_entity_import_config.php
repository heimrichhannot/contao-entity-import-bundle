<?php

$lang = &$GLOBALS['TL_LANG']['tl_entity_import_config'];

/**
 * Fields
 */
$lang['title'][0]             = 'Title';
$lang['title'][1]             = 'Enter the title of the import here.';
$lang['targetTable'][0]       = 'Target table';
$lang['targetTable'][1]       = 'Select the table into which you want to import.';
$lang['mergeTable'][0]        = 'Merge during import';
$lang['mergeTable'][1]        = 'Select this option if you want the import to merge existing records with the records being imported.';
$lang['purgeBeforeImport'][0] = 'Delete data before import';
$lang['purgeBeforeImport'][1] = 'Select this option if the existing data should be deleted before import.';
$lang['purgeWhereClause'][0]  = 'WHERE conditions for deletion';
$lang['purgeWhereClause'][1]  = 'Enter SQL conditions in the form "pid=27 AND id=1" that should apply to the deletion of records before each import.';

$lang['mergeIdentifierFields'][0]           = 'Merge identification fields';
$lang['mergeIdentifierFields'][0]           = 'Select the fields to be used for finding existing records (e.g. e-mail, ID, first name + lastname, ...).';
$lang['mergeIdentifierFields']['source'][0] = 'Source field';
$lang['mergeIdentifierFields']['source'][1] = 'Pick the source field from the external source here.';
$lang['mergeIdentifierFields']['target'][0] = 'Field in target table';
$lang['mergeIdentifierFields']['target'][1] = 'Select the target field into which you want to import.';

$lang['importMode'][0] = 'Import settings';
$lang['importMode'][1] = 'Select the settings for the import here.';

$lang['useCron'][0] = 'Use cronjob';
$lang['useCron'][1] = 'Select this option to trigger the importer by cronjob.';
$lang['cronInterval'][0] = 'Cron-Interval';
$lang['cronInterval'][1] = 'Select the interval at which the import is to be performed.';

/**
 * Reference
 */
$lang['reference'] = [
    'importMode' => [
        'insert' => 'Create new records when importing',
        'merge'  => 'Merge records when importing',
        'purge'  => 'Delete the records in the target table before importing',
    ],
    'cronInterval' => [
        'minutely' => 'minutely',
        'hourly' => 'hourly',
        'daily' => 'daily',
        'weekly' => 'weekly',
        'monthly' => 'monthly'
    ]
];

/**
 * Messages
 */
$lang['importConfirm'] = 'Should the import ID %s really be performed?';

/**
 * Errors
 */
$lang['error']['errorMessage']        = 'An error occurred during import: %s.';
$lang['error']['notInitialized']      = 'Importer is not yet initialized.';
$lang['error']['tableDoesNotExist']     = 'The target table does not exist.';
$lang['error']['tableFieldsDiffer']   = 'The destination and source fields are different.';
$lang['error']['noIdentifierFields']  = 'Identifier fields not set.';
$lang['error']['successfulImport']    = 'Successful import of %s entries.';
$lang['error']['emptyFile']           = 'Data for import not available.';
$lang['error']['errorImport']         = 'Incorrect import of %s entries. Error: %s';
$lang['error']['delimiter']           = 'Delimiter for csv is not defined.';
$lang['error']['enclosure']           = 'Enclosure for csv is not defined.';
$lang['error']['escape']              = 'Escape for csv is not defined.';
$lang['error']['filePathNotProvided'] = 'The path to the file was not found.';
$lang['error']['modeNotSet']          = 'The import mode is not set.';

/**
 * Backend Modules
 */
$lang['import'][0] = 'Perform an import';
$lang['import'][1] = 'Execute import ID %s.';
$lang['headline']  = 'Import ID %s';
$lang['label']     = 'Click &quot;Perform Import&quot; to start the import process.';


/**
 * Buttons
 */
$lang['new'][0]    = 'New importer';
$lang['new'][1]    = 'Create a new importer';
$lang['show'][0]   = 'Importer details';
$lang['show'][1]   = 'Show details of importer ID %s';
$lang['edit'][0]   = 'Edit importer';
$lang['edit'][1]   = 'Edit importer ID %s';
$lang['copy'][0]   = 'Copy importer';
$lang['copy'][1]   = 'Duplicate importer ID %s';
$lang['delete'][0] = 'Delete importer';
$lang['delete'][1] = 'Delete importer ID %s';
$lang['dryRun'][0] = 'Test run';
$lang['dryRun'][1] = 'Execute test run';
