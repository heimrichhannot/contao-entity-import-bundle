<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

\HeimrichHannot\UtilsBundle\Dca\DateAddedField::register('tl_entity_import_source');

$connection = \Contao\System::getContainer()->get('doctrine.dbal.default_connection');
$connectionParams = $connection->getParams();

$GLOBALS['TL_DCA']['tl_entity_import_source'] = [
    // Config
    'config' => [
        'dataContainer' => \Contao\DC_Table::class,
        'ctable' => ['tl_entity_import_config'],
        'enableVersioning' => true,
        'onload_callback' => [[\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::class, 'initPalette']],
        'onsubmit_callback' => [
            [\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::class, 'setPreset'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => 2,
            'fields' => ['title'],
            'flag' => 1,
            'panelLayout' => 'search,limit',
        ],
        'label' => [
            'fields' => ['title', 'type'],
            'format' => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
        ],
        'global_operations' => [
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();" accesskey="e"',
            ],
        ],
        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['edit'],
                'href' => 'table=tl_entity_import_config',
                'icon' => 'edit.gif',
            ],
            'editheader' => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['editheader'],
                'href' => 'act=edit',
                'icon' => 'header.gif',
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.gif',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if (!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null).'\')) return false; Backend.getScrollOffset();"',
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        '__selector__' => ['type', 'retrievalType', 'fileType'],
        'default' => '{title_legend},title,type;',
        HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::TYPE_DATABASE => '{title_legend},title,type;{db_legend},dbDriver,dbHost,dbUser,dbPass,dbDatabase,dbPconnect,dbCharset,dbPort,dbSocket,dbSourceTableExplanation,dbSourceTable,dbSourceTableWhere,addDcMultilingualSupport,addChangeLanguageSupport,fieldMappingCopier,fieldMappingPresets,fieldMapping;',
        HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::TYPE_FILE => '{title_legend},title,type;{file_legend},retrievalType;',
    ],

    // Subpalettes
    'subpalettes' => [
        'retrievalType_http' => 'sourceUrl,httpMethod,httpAuth,fileType',
        'retrievalType_contao_file_system' => 'fileSRC,fileType',
        'retrievalType_absolute_path' => 'absolutePath',
        'fileType_csv' => 'fileContent,csvHeaderRow,csvSkipEmptyLines,csvDelimiter,csvEnclosure,csvEscape,fieldMappingCopier,fieldMappingPresets,fieldMapping',
        'fileType_json' => 'fileContent,pathToDataArray,fieldMappingCopier,fieldMappingPresets,fieldMapping',
        'fileType_rss' => 'fileContent,pathToDataArray,fieldMappingCopier,fieldMappingPresets,fieldMapping',
        'fileType_xml' => 'fileContent,pathToDataArray,fieldMappingCopier,fieldMappingPresets,fieldMapping',
    ],
    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title' => [
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['title'],
            'search' => true,
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 128, 'tl_class' => 'w50'],
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        'type' => [
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['type'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options' => \HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::TYPES,
            'reference' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['type'],
            'eval' => ['submitOnChange' => true, 'includeBlankOption' => true, 'tl_class' => 'w50', 'mandatory' => true],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'dbDriver' => [
            'exclude' => true,
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['dbDriver'],
            'inputType' => 'select',
            'default' => 'pdo_mysql',
            'options' => ['pdo_mysql'],
            'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(12) NOT NULL default ''",
        ],
        'dbHost' => [
            'exclude' => true,
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['dbHost'],
            'inputType' => 'text',
            'default' => $connectionParams['host'],
            'eval' => ['mandatory' => true, 'maxlength' => 64, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'dbUser' => [
            'exclude' => true,
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['dbUser'],
            'inputType' => 'text',
            'default' => $connectionParams['user'] ?? '',
            'eval' => ['mandatory' => true, 'maxlength' => 64, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'dbPass' => [
            'exclude' => true,
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['dbPass'],
            'inputType' => 'text',
            'eval' => ['maxlength' => 64, 'tl_class' => 'w50', 'decodeEntities' => true],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'dbDatabase' => [
            'exclude' => true,
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['dbDatabase'],
            'inputType' => 'text',
            'default' => $connectionParams['dbname'] ?? '',
            'eval' => ['mandatory' => true, 'maxlength' => 64, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'dbPconnect' => [
            'exclude' => true,
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['dbPconnect'],
            'inputType' => 'select',
            'default' => 'false',
            'options' => ['false', 'true'],
            'eval' => ['tl_class' => 'w50'],
            'sql' => "varchar(5) NOT NULL default ''",
        ],
        'dbCharset' => [
            'exclude' => true,
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['dbCharset'],
            'inputType' => 'text',
            'default' => $connectionParams['charset'] ?? '',
            'eval' => ['mandatory' => true, 'maxlength' => 32, 'tl_class' => 'w50'],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'dbPort' => [
            'exclude' => true,
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['dbPort'],
            'inputType' => 'text',
            'default' => $connectionParams['port'] ?? 0,
            'eval' => ['maxlength' => 5, 'tl_class' => 'w50', 'rgxp' => 'digit'],
            'sql' => "int(5) unsigned NOT NULL default '0'",
        ],
        'dbSocket' => [
            'exclude' => true,
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['dbSocket'],
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'dbSourceTableExplanation' => [
            'inputType' => 'explanation',
            'eval' => [
                'text' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['dbSourceTableExplanation'],
                'class' => 'tl_info',
                'tl_class' => 'long clr',
            ],
        ],
        'dbSourceTable' => [
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['dbSourceTable'],
            'search' => true,
            'exclude' => true,
            'inputType' => 'select',
            'eval' => ['tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true, 'submitOnChange' => true],
            'options_callback' => [\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::class, 'getAllTargetTables'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'dbSourceTableWhere' => [
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['dbSourceTableWhere'],
            'inputType' => 'textarea',
            'exclude' => true,
            'eval' => ['class' => 'monospace', 'rte' => 'ace|sql', 'tl_class' => 'w50', 'decodeEntities' => true],
            'explanation' => 'insertTags',
            'sql' => 'text NULL',
        ],
        'externalUrl' => [
            'exclude' => true,
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['externalUrl'],
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'rgxp' => 'url', 'decodeEntities' => true],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'retrievalType' => [
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['retrievalType'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options' => \HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::RETRIEVAL_TYPES,
            'reference' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['retrievalType'],
            'eval' => ['submitOnChange' => true, 'includeBlankOption' => true, 'tl_class' => 'w50', 'mandatory' => true],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'fileType' => [
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['fileType'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options' => \HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::FILETYPES,
            'reference' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['fileType'],
            'eval' => ['submitOnChange' => true, 'includeBlankOption' => true, 'tl_class' => 'w50', 'mandatory' => true],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'sourceUrl' => [
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['sourceUrl'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'text',
            'eval' => ['submitOnChange' => true, 'includeBlankOption' => true, 'tl_class' => 'clr w50', 'rgxp' => 'url'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'httpMethod' => [
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['httpMethod'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options' => [
                'get',
                'post',
            ],
            'reference' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['reference']['httpMethod'],
            'eval' => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'submitOnChange' => true],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'httpAuth' => [
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['httpAuth'],
            'inputType' => 'multiColumnEditor',
            'eval' => [
                'tl_class' => 'long clr',

                'multiColumnEditor' => [
                    'minRowCount' => 0,
                    'maxRowCount' => 1,
                    'sortable' => true,
                    'fields' => [
                        'name' => [
                            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['httpAuth']['username'],
                            'exclude' => true,
                            'inputType' => 'text',
                            'eval' => [
                                'groupStyle' => 'width: 49%',
                            ],
                        ],
                        'value' => [
                            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['httpAuth']['password'],
                            'exclude' => true,
                            'inputType' => 'text',
                            'eval' => [
                                'groupStyle' => 'width: 49%',
                            ],
                        ],
                    ],
                ],
            ],
            'sql' => 'blob NULL',
        ],
        'fileSRC' => [
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['fileSRC'],
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => ['filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => true, 'tl_class' => 'w50 clr', 'submitOnChange' => true],
            'sql' => 'binary(16) NULL',
        ],
        'absolutePath' => [
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['absolutePath'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'text',
            'eval' => ['submitOnChange' => true, 'includeBlankOption' => true, 'tl_class' => 'clr w50'],
        ],
        'fileContent' => [
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['fileContent'],
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => [
                'readonly' => true,
                'allowHtml' => true,
                'class' => 'monospace',
                'rte' => 'ace|json',
                'helpwizard' => false,
                'tl_class' => 'long clr',
            ],
            'load_callback' => [[\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::class, 'onLoadFileContent']],
        ],
        'pathToDataArray' => [
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['pathToDataArray'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'clr w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'fieldMappingCopier' => [
            'inputType' => 'fieldValueCopier',
            'exclude' => true,
            'eval' => [
                'fieldValueCopier' => [
                    'table' => 'tl_entity_import_source',
                    'field' => 'fieldMapping',
                    'config' => [
                        'labelPattern' => '%title% (ID %id%)',
                    ],
                    'options_callback' => ['huh.field_value_copier.util.field_value_copier_util', 'getOptions'],
                ],
                'tl_class' => 'clr w50',
            ],
        ],
        'fieldMappingPresets' => [
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['fieldMappingPresets'],
            'exclude' => true,
            'inputType' => 'select',
            'reference' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['reference']['fieldMappingPresets'],
            // options can be passed in via event listener
            'eval' => ['tl_class' => 'w50', 'includeBlankOption' => true, 'submitOnChange' => true, 'onchange' => "if(!confirm('".($GLOBALS['TL_LANG']['MSC']['entityImport']['presetConfirm'] ?? null)."')) {this.selectedIndex = 0; return false;}"],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'fieldMapping' => [
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['fieldMapping'],
            'inputType' => 'multiColumnEditor',
            'exclude' => true,
            'eval' => [
                'tl_class' => 'long clr',
                'multiColumnEditor' => [
                    'sortable' => true,
                    'minRowCount' => 0,
                    'palettes' => [
                        '__selector__' => ['valueType'],
                        'default' => 'name, valueType',
                    ],
                    'subpalettes' => [
                        'valueType_source_value' => 'sourceValue',
                        'valueType_static_value' => 'staticValue',
                    ],
                    'fields' => [
                        'name' => [
                            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['fieldMapping']['name'],
                            'inputType' => 'text',
                            'eval' => [
                                'groupStyle' => 'width: 38%',
                                'mandatory' => true,
                            ],
                        ],
                        'valueType' => [
                            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['fieldMapping']['valueType'],
                            'exclude' => true,
                            'inputType' => 'select',
                            'options' => [
                                'source_value',
                                'static_value',
                            ],
                            'reference' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['reference']['valueType'],
                            'eval' => [
                                'groupStyle' => 'width: 20%',
                                'mandatory' => true,
                                'includeBlankOption' => true,
                                'submitOnChange' => true,
                            ],
                            'sql' => "varchar(64) NOT NULL default ''",
                        ],
                        'sourceValue' => [
                            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['fieldMapping']['sourceValue'],
                            'inputType' => 'text',
                            'eval' => [
                                'groupStyle' => 'width: 38%',
                            ],
                        ],
                        'staticValue' => [
                            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['fieldMapping']['staticValue'],
                            'inputType' => 'text',
                            'eval' => [
                                'groupStyle' => 'width: 38%',
                                'mandatory' => true,
                            ],
                        ],
                    ],
                ],
            ],
            'sql' => 'blob NULL',
        ],
        'csvHeaderRow' => [
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['csvHeaderRow'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => [
                'tl_class' => 'w50',
                'submitOnChange' => true,
            ],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'csvSkipEmptyLines' => [
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['csvSkipEmptyLines'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => [
                'tl_class' => 'w50',
            ],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'csvDelimiter' => [
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['csvDelimiter'],
            'exclude' => true,
            'inputType' => 'text',
            'default' => ',',
            'eval' => [
                'decodeEntities' => true,
                'maxlength' => 1,
                'tl_class' => 'w50',
                'submitOnChange' => true,
            ],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'csvEnclosure' => [
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['csvEnclosure'],
            'exclude' => true,
            'inputType' => 'text',
            'default' => '"',
            'eval' => [
                'decodeEntities' => true,
                'tl_class' => 'w50',
                'maxlength' => 1,
                'submitOnChange' => true,
            ],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'csvEscape' => [
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['csvEscape'],
            'exclude' => true,
            'inputType' => 'text',
            'default' => ';',
            'eval' => [
                'decodeEntities' => true,
                'tl_class' => 'w50',
                'submitOnChange' => true,
                'maxlength' => 1,
            ],
            'sql' => "char(1) NOT NULL default ''",
        ],
    ],
];

$dca = &$GLOBALS['TL_DCA']['tl_entity_import_source'];

// DC_Multilingual
if (class_exists('\Terminal42\DcMultilingualBundle\Terminal42DcMultilingualBundle')) {
    $dca['fields']['addDcMultilingualSupport'] = [
        'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['addDcMultilingualSupport'],
        'exclude' => true,
        'inputType' => 'checkbox',
        'eval' => ['tl_class' => 'w50'],
        'sql' => "char(1) NOT NULL default ''",
    ];
}

// change_language
if (class_exists('\Terminal42\ChangeLanguage\Language')) {
    $dca['fields']['addChangeLanguageSupport'] = [
        'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['addChangeLanguageSupport'],
        'exclude' => true,
        'inputType' => 'checkbox',
        'eval' => ['tl_class' => 'w50'],
        'sql' => "char(1) NOT NULL default ''",
    ];
}
