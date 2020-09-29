<?php

$lang = &$GLOBALS['TL_LANG']['tl_entity_import_source'];

/**
 * Fields
 */
$lang['title'][0]    = 'Title';
$lang['title'][1]    = 'Enter the title of the source here.';
$lang['type'][0]     = 'Type';
$lang['type'][1]     = 'Select the type of import here.';
$lang['fileType'][0] = 'File type';
$lang['fileType'][1] = 'Select the type of file here.';

$lang['csvHeaderRow'][0] = 'Header data record';
$lang['csvHeaderRow'][1] = 'The first record contains the column names.';
$lang['csvSkipEmptyLines'][0] = 'Skip empty lines';
$lang['csvSkipEmptyLines'][1] = 'Should empty lines be skipped?';
$lang['csvDelimiter'][0] = 'Field delimiter';
$lang['csvDelimiter'][1] = 'Enter the field delimiter here.';
$lang['csvEnclosure'][0] = 'Enclosure';
$lang['csvEnclosure'][1] = 'Enter the enclosure here.';
$lang['csvEscape'][0]    = 'Escape';
$lang['csvEscape'][1]    = 'Enter the separator for converting separator-separated field values. If the corresponding check mark is set in the field mapping, values like "1;4;5" are transformed to a serialized array.';

$lang['pathToDataArray'][0]             = 'Path to the data';
$lang['pathToDataArray'][1]             = 'Enter the path of the data in the file here. Is necessary if the data to be imported is not in the first level.';
$lang['fieldMapping'][0]                = 'Field mapping';
$lang['fieldMapping'][1]                = 'Enter the assignment of the fields from the source here.';
$lang['fieldMapping']['name'][0]        = 'Name';
$lang['fieldMapping']['name'][1]        = 'Enter the name of the value for further processing here.';
$lang['fieldMapping']['valueType'][0]   = 'Type of the value';
$lang['fieldMapping']['valueType'][1]   = 'Pick the type of the value here. For dynamic, the value is taken from the record. With static, the content of the field is taken as the value.';
$lang['fieldMapping']['sourceValue'][0] = 'Value from the source';
$lang['fieldMapping']['sourceValue'][1] = 'Enter the location of the value in the source here.';
$lang['fieldMapping']['staticValue'][0] = 'Static value';
$lang['fieldMapping']['staticValue'][1] = 'Enter the value that is to be entered immediately in all data records.';

$lang['fileContent'][0] = 'File preview';
$lang['fileContent'][1] = 'Here you can see the contents of the selected file. The entire file is not displayed.';

$lang['retrievalType'][0] = 'Source';
$lang['retrievalType'][1] = 'Select the type of file source here.';

$lang['retrievalType'][\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::RETRIEVAL_TYPE_HTTP]               = 'HTTP';
$lang['retrievalType'][\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::RETRIEVAL_TYPE_CONTAO_FILE_SYSTEM] = 'Contao file management';
$lang['retrievalType'][\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::RETRIEVAL_TYPE_ABSOLUTE_PATH]      = 'Absolute path';
$lang['type'][\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::TYPE_DATABASE]                              = 'Database';
$lang['type'][\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::TYPE_FILE]                                  = 'File';

$lang['sourceUrl'][0]            = 'Url';
$lang['sourceUrl'][1]            = 'Enter the URL to the file here.';
$lang['absolutePath'][0]         = 'Absolute file path';
$lang['absolutePath'][1]         = 'Enter an absolute file path on the server here.';
$lang['fileSRC'][0]              = 'Select or upload file';
$lang['fileSRC'][1]              = 'Select an existing file here, or upload a new file.';
$lang['dbDriver'][0]             = 'Driver';
$lang['dbDriver'][1]             = 'Select the database driver here.';
$lang['dbHost'][0]               = 'Host';
$lang['dbHost'][1]               = 'Enter the address of the database host here.';
$lang['dbUser'][0]               = 'User';
$lang['dbUser'][1]               = 'Enter an authorized database user here.';
$lang['dbPass'][0]               = 'Password';
$lang['dbPass'][1]               = 'Enter the password of the authorized database user here.';
$lang['dbDatabase'][0]           = 'Database name';
$lang['dbDatabase'][1]           = 'Enter the name of the database here.';
$lang['dbPconnect'][0]           = 'PConnect';
$lang['dbPconnect'][1]           = 'Select here whether you want to use PConnect.';
$lang['dbCharset'][0]            = 'Character set';
$lang['dbCharset'][1]            = 'Select the desired character set here.';
$lang['dbSocket'][0]             = 'Socket';
$lang['dbSocket'][1]             = 'Enter a socket here.';
$lang['externalUrl'][0]          = 'Url';
$lang['externalUrl'][1]          = 'Enter the Url from which the data is to be imported here.';
$lang['httpMethod'][0]           = 'HTTP-Method';
$lang['httpMethod'][1]           = 'Select the HTTP method with which the file should be accessed.';
$lang['httpAuth'][0]             = 'Authentication';
$lang['httpAuth'][1]             = 'Enter the data for the authentication here.';
$lang['httpAuth']['username'][0] = 'Username';
$lang['httpAuth']['username'][1] = 'Enter your user name here.';
$lang['httpAuth']['password'][0] = 'Password';
$lang['httpAuth']['password'][1] = 'Enter your password here.';

/**
 * Reference
 */
$lang['reference'] = [
    'valueType'  => [
        'source_value' => 'dynamic',
        'static_value' => 'static',
    ],
    'httpMethod' => [
        'get'  => 'GET',
        'post' => 'POST',
    ],
];

/**
 * Legends
 */
$lang['title_legend']    = 'Title';
$lang['db_legend']       = 'Database settings';
$lang['external_legend'] = 'External source';
$lang['file_legend']     = 'File';

/**
 * Buttons
 */
$lang['new'][0]        = 'New import source';
$lang['new'][1]        = 'Creating a new import source';
$lang['show'][0]       = 'Import source-Details';
$lang['show'][1]       = 'Show details of import source ID %s';
$lang['editheader'][0] = 'Edit import source';
$lang['editheader'][1] = 'Edit import source ID %s';
$lang['copy'][0]       = 'Copy import source';
$lang['copy'][1]       = 'Duplicate import source ID %s';
$lang['delete'][0]     = 'Delete import source';
$lang['delete'][1]     = 'Delete import source ID %s';
