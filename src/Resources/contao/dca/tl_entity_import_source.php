<?php

$GLOBALS['TL_DCA']['tl_entity_import_source'] = [

    // Config
    'config'      => [
        'dataContainer'    => 'Table',
        'enableVersioning' => true,
        'onload_callback'  => [[\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::class, 'initPalette']],
        'sql'              => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],

    // List
    'list'        => [
        'sorting'           => [
            'mode'        => 2,
            'fields'      => ['title'],
            'flag'        => 1,
            'panelLayout' => 'search,limit',
        ],
        'label'             => [
            'fields' => ['title', 'type'],
            'format' => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
            //            'label_callback' => [\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::class, 'addDate', true],
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();" accesskey="e"',
            ],
        ],
        'operations'        => [
            'edit'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['edit'],
                'href'  => 'table=tl_entity_import_config',
                'icon'  => 'edit.gif',
            ],
            'editheader' => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['editheader'],
                'href'  => 'act=edit',
                'icon'  => 'header.gif',
            ],
            'copy'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ],
            'delete'     => [
                'label'      => &$GLOBALS['TL_LANG']['tl_entity_import_source']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
            ],
            'show'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
        ],
    ],

    // Palettes
    'palettes'    => [
        '__selector__'                                                                             => ['type', 'retrievalType', 'fileType'],
        'default'                                                                                  => '{title_legend},title,type;',
        HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::TYPE_DATABASE => '{title_legend},title,type;{db_legend},dbDriver,dbHost,dbUser,dbPass,dbDatabase,dbPconnect,dbCharset,dbPort,dbSocket',
        HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::TYPE_FILE     => '{title_legend},title,type;{file_legend},retrievalType',
    ],

    // Subpalettes
    'subpalettes' => [
        'retrievalType_http'               => 'sourceUrl,httpMethod,httpAuth,fileType',
        'retrievalType_contao_file_system' => 'fileSRC,fileType',
        'retrievalType_absolute_path'      => 'absolutePath',
        'fileType_csv'                     => 'fileContent,csvHeaderRow,csvDelimiter,csvEnclosure,csvEscape,fieldMapping',
        'fileType_json'                    => 'fileContent,pathToDataArray,fieldMapping',
    ],
    // Fields
    'fields'      => [
        'id'              => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'tstamp'          => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title'           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_source']['title'],
            'search'    => true,
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 128, 'tl_class' => 'w50'],
            'sql'       => "varchar(128) NOT NULL default ''",
        ],
        'type'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_source']['type'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'options'   => [
                \HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::TYPE_DATABASE,
                \HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::TYPE_FILE,
            ],
            'reference' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['type'],
            'eval'      => ['submitOnChange' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'dbDriver'        => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_source']['dbDriver'],
            'inputType' => 'select',
            'default'   => version_compare(VERSION, '4.0', '<') ? \Config::get('dbDriver') : 'pdo_mysql',
            'options'   => version_compare(VERSION, '4.0', '<') ? ['MySQLi', 'MySQL'] : ['pdo_mysql'],
            'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(12) NOT NULL default ''",
        ],
        'dbHost'          => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_source']['dbHost'],
            'inputType' => 'text',
            'default'   => \Config::get('dbHost'),
            'eval'      => ['mandatory' => true, 'maxlength' => 64, 'tl_class' => 'w50'],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'dbUser'          => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_source']['dbUser'],
            'inputType' => 'text',
            'default'   => \Config::get('dbUser'),
            'eval'      => ['mandatory' => true, 'maxlength' => 64, 'tl_class' => 'w50'],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'dbPass'          => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_source']['dbPass'],
            'inputType' => 'text',
            'eval'      => ['maxlength' => 64, 'tl_class' => 'w50'],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'dbDatabase'      => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_source']['dbDatabase'],
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 64, 'tl_class' => 'w50'],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'dbPconnect'      => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_source']['dbPconnect'],
            'inputType' => 'select',
            'default'   => 'false',
            'options'   => ['false', 'true'],
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "varchar(5) NOT NULL default ''",
        ],
        'dbCharset'       => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_source']['dbCharset'],
            'inputType' => 'text',
            'default'   => \Config::get('dbCharset'),
            'eval'      => ['mandatory' => true, 'maxlength' => 32, 'tl_class' => 'w50'],
            'sql'       => "varchar(32) NOT NULL default ''",
        ],
        'dbPort'          => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_source']['dbPort'],
            'inputType' => 'text',
            'default'   => \Config::get('dbPort'),
            'eval'      => ['maxlength' => 5, 'tl_class' => 'w50', 'rgxp' => 'digit'],
            'sql'       => "int(5) unsigned NOT NULL default '0'",
        ],
        'dbSocket'        => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_source']['dbSocket'],
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'externalUrl'     => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_source']['externalUrl'],
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50', 'rgxp' => 'url'],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'retrievalType'   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_source']['retrievalType'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'options'   => [
                \HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::RETRIEVAL_TYPE_HTTP,
                \HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::RETRIEVAL_TYPE_CONTAO_FILE_SYSTEM,
                \HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::RETRIEVAL_TYPE_ABSOLUTE_PATH,
            ],
            'reference' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['retrievalType'],
            'eval'      => ['submitOnChange' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'fileType'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_source']['fileType'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'options'   => [
                \HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::FILETYPE_CSV,
                \HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::FILETYPE_JSON,
            ],
            'reference' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['fileType'],
            'eval'      => ['submitOnChange' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'sourceUrl'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_source']['sourceUrl'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'text',
            'eval'      => ['submitOnChange' => true, 'includeBlankOption' => true, 'tl_class' => 'clr w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'httpMethod'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_source']['httpMethod'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'options'   => [
                'get',
                'post',
            ],
            'reference' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['reference']['httpMethod'],
            'eval'      => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'submitOnChange' => true],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'httpAuth'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_source']['httpAuth'],
            'inputType' => 'multiColumnEditor',
            'eval'      => [
                'tl_class'          => 'long clr',

                'multiColumnEditor' => [
                    'minRowCount' => 0,
                    'maxRowCount' => 1,
                    'sortable'    => true,
                    'fields'      => [
                        'name'  => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_source']['httpAuth']['username'],
                            'exclude'   => true,
                            'inputType' => 'text',
                            'eval'      => [
                                'groupStyle' => 'width: 49%',
                            ],
                        ],
                        'value' => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_source']['httpAuth']['password'],
                            'exclude'   => true,
                            'inputType' => 'text',
                            'eval'      => [
                                'groupStyle' => 'width: 49%',
                            ],
                        ],
                    ],
                ],
            ],
            'sql'       => "blob NULL",
        ],
        'fileSRC'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_source']['fileSRC'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => ['filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => true, 'tl_class' => 'w50 clr', 'submitOnChange' => true],
            'sql'       => "binary(16) NULL",
        ],
        'absolutePath'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_source']['absolutePath'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'text',
            'eval'      => ['submitOnChange' => true, 'includeBlankOption' => true, 'tl_class' => 'clr w50'],
        ],
        'fileContent'     => [
            'label'         => &$GLOBALS['TL_LANG']['tl_entity_import_source']['fileContent'],
            'exclude'       => true,
            'inputType'     => 'textarea',
            'eval'          => [
                'disabled'   => true,
                'rows'  => 20,
                'allowHtml'  => true,
                'class'      => 'monospace',
                'rte'        => 'ace|json',
                'helpwizard' => false,
                'tl_class'   => 'long clr',
            ],
            'load_callback' => [[\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer::class, 'onLoadFileContent']],
        ],
        'pathToDataArray' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_source']['pathToDataArray'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'fieldMapping'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_source']['fieldMapping'],
            'inputType' => 'multiColumnEditor',
            'eval'      => [
                'tl_class'          => 'long clr',
                'multiColumnEditor' => [
                    'sortable'    => true,
                    'palettes'    => [
                        '__selector__' => ['valueType'],
                        'default'      => 'name, valueType',
                    ],
                    'subpalettes' => [
                        'valueType_source_value' => 'sourceValue',
                        'valueType_static_value' => 'staticValue',
                    ],
                    'fields'      => [
                        'name'        => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_source']['fieldMapping']['name'],
                            'inputType' => 'text',
                            'eval'      => [
                                'groupStyle' => 'width: 38%',
                            ],
                        ],
                        'valueType'   => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_source']['fieldMapping']['valueType'],
                            'exclude'   => true,
                            'filter'    => true,
                            'inputType' => 'select',
                            'options'   => [
                                'source_value',
                                'static_value',
                            ],
                            'reference' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['reference']['valueType'],
                            'eval'      => [
                                'groupStyle'         => 'width: 20%',
                                'mandatory'          => true,
                                'includeBlankOption' => true,
                                'submitOnChange'     => true,
                            ],
                            'sql'       => "varchar(64) NOT NULL default ''",
                        ],
                        'sourceValue' => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_source']['fieldMapping']['sourceValue'],
                            'inputType' => 'text',
                            'eval'      => [
                                'groupStyle' => 'width: 38%',
                            ],
                        ],
                        'staticValue' => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_source']['fieldMapping']['staticValue'],
                            'inputType' => 'text',
                            'eval'      => [
                                'groupStyle' => 'width: 38%',
                            ],
                        ],
                    ],
                ],
            ],
            'sql'       => "blob NULL",
        ],
        'csvHeaderRow'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_source']['csvHeaderRow'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => [
                'tl_class'       => 'w50',
                'submitOnChange' => true,
            ],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'csvDelimiter'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_source']['csvDelimiter'],
            'exclude'   => true,
            'inputType' => 'text',
            'default'   => ',',
            'eval'      => [
                'maxlength'      => '1',
                'tl_class'       => 'w50',
                'submitOnChange' => true,
            ],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'csvEnclosure'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_source']['csvEnclosure'],
            'exclude'   => true,
            'inputType' => 'text',
            'default'   => '"',
            'eval'      => [
                'tl_class'       => 'w50',
                'maxlength'      => '1',
                'submitOnChange' => true,
            ],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'csvEscape'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_source']['csvEscape'],
            'exclude'   => true,
            'inputType' => 'text',
            'default'   => ';',
            'eval'      => [
                'tl_class'       => 'w50',
                'submitOnChange' => true,
                'maxlength'      => '1',
            ],
            'sql'       => "char(1) NOT NULL default ''",
        ],
    ],
];