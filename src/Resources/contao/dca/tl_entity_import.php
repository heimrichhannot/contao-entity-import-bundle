<?php

$GLOBALS['TL_DCA']['tl_entity_import'] = [

    // Config
    'config'      => [
        'dataContainer'    => 'Table',
        'enableVersioning' => true,
        //        'ctable'           => ['tl_entity_import_config'],
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
            //            'label_callback' => [\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportContainer::class, 'addDate', true],
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
//            'edit'       => [
//                'label' => &$GLOBALS['TL_LANG']['tl_entity_import']['edit'],
//                'href'  => 'table=tl_entity_import_config',
//                'icon'  => 'edit.gif',
//            ],
'editheader' => [
    'label' => &$GLOBALS['TL_LANG']['tl_entity_import']['editheader'],
    'href'  => 'act=edit',
    'icon'  => 'header.gif',
],
'copy'       => [
    'label' => &$GLOBALS['TL_LANG']['tl_entity_import']['copy'],
    'href'  => 'act=copy',
    'icon'  => 'copy.gif',
],
'delete'     => [
    'label'      => &$GLOBALS['TL_LANG']['tl_entity_import']['delete'],
    'href'       => 'act=delete',
    'icon'       => 'delete.gif',
    'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
],
'show'       => [
    'label' => &$GLOBALS['TL_LANG']['tl_entity_import']['show'],
    'href'  => 'act=show',
    'icon'  => 'show.gif',
],
        ],
    ],

    // Palettes
    'palettes'    => [
        '__selector__'                                                                       => ['type', 'sourceType', 'fileType', 'httpFileType'],
        'default'                                                                            => '{title_legend},title,type;',
        HeimrichHannot\EntityImportBundle\DataContainer\EntityImportContainer::TYPE_DATABASE => '{title_legend},title,type;{db_legend},dbDriver,dbHost,dbUser,dbPass,dbDatabase,dbPconnect,dbCharset,dbPort,dbSocket',
        HeimrichHannot\EntityImportBundle\DataContainer\EntityImportContainer::TYPE_FILE     => '{title_legend},title,type;{file_legend},sourceType',
    ],

    // Subpalettes
    'subpalettes' => [
        'sourceType_http'               => 'sourceUrl,httpFileType',
        'sourceType_contao_file_system' => 'fileType',
        'sourceType_absolute_path'      => 'filePath',
        'fileType_csv'                  => 'fileSRC,fileContent,csvHeaderRow,csvFieldSeparator,csvTextSeparator,csvArraySeparator,csvFieldMapping',
        'fileType_json'                 => 'fileSRC,fileContent,jsonFieldMapping',
        'httpFileType_csv'              => 'fileContent',
        'httpFileType_json'             => 'fileContent',
    ],
    // Fields
    'fields'      => [
        'id'                => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'tstamp'            => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title'             => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['title'],
            'search'    => true,
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 128, 'tl_class' => 'w50'],
            'sql'       => "varchar(128) NOT NULL default ''",
        ],
        'type'              => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['type'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'options'   => [
                \HeimrichHannot\EntityImportBundle\DataContainer\EntityImportContainer::TYPE_DATABASE,
                \HeimrichHannot\EntityImportBundle\DataContainer\EntityImportContainer::TYPE_FILE,
            ],
            'reference' => &$GLOBALS['TL_LANG']['tl_entity_import']['type'],
            'eval'      => ['submitOnChange' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'dbDriver'          => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['dbDriver'],
            'inputType' => 'select',
            'default'   => version_compare(VERSION, '4.0', '<') ? \Config::get('dbDriver') : 'pdo_mysql',
            'options'   => version_compare(VERSION, '4.0', '<') ? ['MySQLi', 'MySQL'] : ['pdo_mysql'],
            'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(12) NOT NULL default ''",
        ],
        'dbHost'            => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['dbHost'],
            'inputType' => 'text',
            'default'   => \Config::get('dbHost'),
            'eval'      => ['mandatory' => true, 'maxlength' => 64, 'tl_class' => 'w50'],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'dbUser'            => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['dbUser'],
            'inputType' => 'text',
            'default'   => \Config::get('dbUser'),
            'eval'      => ['mandatory' => true, 'maxlength' => 64, 'tl_class' => 'w50'],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'dbPass'            => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['dbPass'],
            'inputType' => 'text',
            'eval'      => ['maxlength' => 64, 'tl_class' => 'w50'],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'dbDatabase'        => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['dbDatabase'],
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 64, 'tl_class' => 'w50'],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'dbPconnect'        => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['dbPconnect'],
            'inputType' => 'select',
            'default'   => 'false',
            'options'   => ['false', 'true'],
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "varchar(5) NOT NULL default ''",
        ],
        'dbCharset'         => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['dbCharset'],
            'inputType' => 'text',
            'default'   => \Config::get('dbCharset'),
            'eval'      => ['mandatory' => true, 'maxlength' => 32, 'tl_class' => 'w50'],
            'sql'       => "varchar(32) NOT NULL default ''",
        ],
        'dbPort'            => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['dbPort'],
            'inputType' => 'text',
            'default'   => \Config::get('dbPort'),
            'eval'      => ['maxlength' => 5, 'tl_class' => 'w50', 'rgxp' => 'digit'],
            'sql'       => "int(5) unsigned NOT NULL default '0'",
        ],
        'dbSocket'          => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['dbSocket'],
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'externalUrl'       => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['externalUrl'],
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50', 'rgxp' => 'url'],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'sourceType'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['sourceType'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'options'   => [
                \HeimrichHannot\EntityImportBundle\DataContainer\EntityImportContainer::SOURCE_TYPE_HTTP,
                \HeimrichHannot\EntityImportBundle\DataContainer\EntityImportContainer::SOURCE_TYPE_CONTAO_FILE_SYSTEM,
                \HeimrichHannot\EntityImportBundle\DataContainer\EntityImportContainer::SOURCE_TYPE_ABSOLUTE_PATH,
            ],
            'reference' => &$GLOBALS['TL_LANG']['tl_entity_import']['sourceType'],
            'eval'      => ['submitOnChange' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'fileType'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['fileType'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'options'   => [
                \HeimrichHannot\EntityImportBundle\DataContainer\EntityImportContainer::FILETYPE_CSV,
                \HeimrichHannot\EntityImportBundle\DataContainer\EntityImportContainer::FILETYPE_JSON,
            ],
            'reference' => &$GLOBALS['TL_LANG']['tl_entity_import']['fileType'],
            'eval'      => ['submitOnChange' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'httpFileType' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['fileType'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'options'   => [
                \HeimrichHannot\EntityImportBundle\DataContainer\EntityImportContainer::FILETYPE_CSV,
                \HeimrichHannot\EntityImportBundle\DataContainer\EntityImportContainer::FILETYPE_JSON,
            ],
            'reference' => &$GLOBALS['TL_LANG']['tl_entity_import']['fileType'],
            'eval'      => ['submitOnChange' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'save_callback' => [[\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportContainer::class, 'onSaveHttpFileType', true]],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'filePath'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['filePath'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'text',
            'reference' => &$GLOBALS['TL_LANG']['tl_entity_import']['filePath'],
            'eval'      => ['submitOnChange' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'sourceUrl'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['sourceUrl'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'text',
            'reference' => &$GLOBALS['TL_LANG']['tl_entity_import']['sourceUrl'],
            'eval'      => ['submitOnChange' => true, 'includeBlankOption' => true, 'tl_class' => 'clr w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'fileSRC'           => [
            'label'            => &$GLOBALS['TL_LANG']['tl_entity_import']['fileSRC'],
            'exclude'          => true,
            'inputType'        => 'fileTree',
            'eval'             => ['alwaysSave' => true, 'filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => false, 'tl_class' => 'clr'],
            'options_callback' => [\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportContainer::class, 'onOptionsFileSRC', true],
            'load_callback'    => [[\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportContainer::class, 'onLoadFileSRC', true]],
            'sql'              => "binary(16) NULL",
        ],
        'fileContent'   => [
            'label'         => &$GLOBALS['TL_LANG']['tl_entity_import']['jsonFileContent'],
            'exclude'       => true,
            'inputType'     => 'textarea',
            'eval'          => [
                'allowHtml'  => true,
                'class'      => 'monospace',
                'rte'        => 'ace|json',
                'helpwizard' => false,
            ],
            'load_callback' => [[\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportContainer::class, 'onLoadFileContent', true]],
            'sql'           => "blob NULL",
        ],
        'jsonFieldMapping'  => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['jsonFieldMapping'],
            'inputType' => 'multiColumnEditor',
            'eval'      => [
                'multiColumnEditor' => [
                    'sortable' => true,
                    'fields'   => [
                        'name'  => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['jsonFieldMapping']['name'],
                            'inputType' => 'text',
                        ],
                        'value' => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['jsonFieldMapping']['value'],
                            'inputType' => 'text',
                        ],
                    ],
                ],
            ],
            'sql'       => "blob NULL",
        ],
        'csvHeaderRow'   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['csvHeaderRow'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => [
                'tl_class'       => 'w50',
                'submitOnChange' => true,
            ],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'csvFieldSeparator' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['csvFieldSeparator'],
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
        'csvTextSeparator'  => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['csvTextSeparator'],
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
        'csvArraySeparator' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['csvArraySeparator'],
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
        'csvFieldMapping'   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['csvFieldMapping'],
            'inputType' => 'multiColumnEditor',
            'eval'      => [
                'tl_class' => 'w100 clr',
                'multiColumnEditor' => [
                    'sortable' => true,
                    'fields'   => [
                        'name'  => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['csvFieldMapping']['name'],
                            'inputType' => 'text',
                        ],
                        'value' => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['csvFieldMapping']['value'],
                            'inputType' => 'text',
                        ],
                    ],
                ],
            ],
            'sql'       => "blob NULL",
        ]
    ],
];