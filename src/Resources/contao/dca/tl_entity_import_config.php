<?php

$GLOBALS['TL_DCA']['tl_entity_import_config'] = [
    'config'      => [
        'dataContainer'     => 'Table',
        'enableVersioning'  => true,
        'ptable'            => 'tl_entity_import_source',
        'onsubmit_callback' => [
            ['huh.utils.dca', 'setDateAdded'],
        ],
        'oncopy_callback'   => [
            ['huh.utils.dca', 'setDateAddedOnCopy'],
        ],
        'sql'               => [
            'keys' => [
                'id'  => 'primary',
                'pid' => 'index',
            ],
        ],
    ],
    'list'        => [
        'sorting'           => [
            'mode'                  => 4,
            'fields'                => ['title DESC'],
            'headerFields'          => ['title'],
            'panelLayout'           => 'filter;sort,search,limit',
            'child_record_callback' => [\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer::class, 'listItems'],
            'disableGrouping'       => true,
        ],
        'label'             => [
            'fields' => ['title'],
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
            'editheader' => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_config']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ],
            'copy'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_config']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ],
            'delete'     => [
                'label'      => &$GLOBALS['TL_LANG']['tl_entity_import_config']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
            ],
            'show'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_config']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
            'dryRun'     => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_config']['dryRun'],
                'href'  => 'key=dryRun',
                'icon'  => 'important.svg',
            ],
            'import'     => [
                'label'      => &$GLOBALS['TL_LANG']['tl_entity_import_config']['import'],
                'href'       => 'key=import',
                'icon'       => 'store.svg',
                'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['tl_entity_import_config']['importConfirm'] . '\')) return false; Backend.getScrollOffset();"',
            ],
        ],
    ],
    'palettes'    => [
        '__selector__' => ['importMode', 'purgeBeforeImport', 'useCron'],
        'default'      => '{general_legend},title,targetTable,importMode,useCron;',
    ],
    'subpalettes' => [
        'importMode_insert' => 'purgeBeforeImport',
        'importMode_merge' => 'mergeIdentifierFields',
        'purgeBeforeImport' => 'purgeWhereClause',
        'useCron' => 'cronInterval'
    ],
    'fields'      => [
        'id'                    => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'pid'                   => [
            'foreignKey' => 'tl_entity_import_source.title',
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'belongsTo', 'load' => 'eager'],
        ],
        'dateAdded'             => [
            'label'   => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag'    => 6,
            'eval'    => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql'     => "int(10) unsigned NOT NULL default '0'",
        ],
        'tstamp'                => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title'                 => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['title'],
            'search'    => true,
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 128, 'tl_class' => 'w50'],
            'sql'       => "varchar(128) NOT NULL default ''",
        ],
        'targetTable'           => [
            'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_config']['targetTable'],
            'search'           => true,
            'exclude'          => true,
            'inputType'        => 'select',
            'eval'             => ['mandatory' => true, 'submitOnChange' => true, 'tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true],
            'options_callback' => [\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer::class, 'getAllTargetTables'],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'fieldMapping'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['fieldMapping'],
            'inputType' => 'multiColumnEditor',
            'eval'      => [
                'tl_class'          => 'long clr',
                'multiColumnEditor' => [
                    'sortable'    => true,
                    'palettes'    => [
                        '__selector__' => ['valueType'],
                        'default'      => 'columnName, valueType',
                    ],
                    'subpalettes' => [
                        'valueType_source_value' => 'mappingValue',
                        'valueType_static_value' => 'staticValue',
                    ],
                    'fields'      => [
                        'columnName'           => [
                            'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_config']['fieldMapping']['columnName'],
                            'filter'                  => true,
                            'exclude'          => true,
                            'inputType'        => 'select',
                            'options' => [],
                            'options_callback' => [\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer::class, 'getTargetFields'],
                            'eval'             => ['mandatory' => true, 'submitOnChange' => true, 'groupStyle' => 'width: 38%', 'chosen' => true, 'includeBlankOption' => true],
                        ],
                        'valueType'   => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['fieldMapping']['valueType'],
                            'exclude'   => true,
                            'inputType' => 'select',
                            'options'   => [
                                'source_value',
                                'static_value',
                            ],
                            'reference' => &$GLOBALS['TL_LANG']['tl_entity_import_config']['reference']['valueType'],
                            'eval'      => [
                                'groupStyle'         => 'width: 20%',
                                'mandatory'          => true,
                                'includeBlankOption' => true,
                                'submitOnChange'     => true,
                            ],

                        ],
                        'mappingValue' => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['fieldMapping']['mappingValue'],
                            'inputType' => 'select',
                            'options_callback' => [\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer::class, 'getSourceFields'],
                            'eval'      => [
                                'groupStyle' => 'width: 38%',
                                'mandatory' => true,
                                'includeBlankOption' => true,
                                'chosen' => true
                            ],
                        ],
                        'staticValue' => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['fieldMapping']['staticValue'],
                            'inputType' => 'text',
                            'eval'      => [
                                'groupStyle' => 'width: 38%',
                                'mandatory' => true
                            ],
                        ],
                    ],
                ],
            ],
            'sql'       => "blob NULL",
        ],
        'importMode'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['importMode'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => [
                'insert',
                'merge',
            ],
            'reference' => &$GLOBALS['TL_LANG']['tl_entity_import_config']['reference']['importMode'],
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true, 'mandatory' => true],
            'sql'       => "varchar(16) NOT NULL default ''",
        ],
        'purgeBeforeImport'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['purgeBeforeImport'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'clr w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'purgeWhereClause'        => [
            'label'       => &$GLOBALS['TL_LANG']['tl_entity_import_config']['purgeWhereClause'],
            'inputType'   => 'textarea',
            'exclude'     => true,
            'eval'        => ['class' => 'monospace', 'rte' => 'ace', 'tl_class' => 'clr long'],
            'explanation' => 'insertTags',
            'sql'         => "text NULL",
        ],
        'mergeIdentifierFields' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['mergeIdentifierFields'],
            'inputType' => 'multiColumnEditor',
            'exclude'   => true,
            'eval'      => [
                'multiColumnEditor' => [
                    'fields' => [
                        'source' => [
                            'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_config']['mergeIdentifierFields']['source'],
                            'inputType'        => 'select',
                            'options_callback' => [\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer::class, 'getSourceFields'],
                            'eval'             => [
                                'groupStyle'         => 'width: 49%',
                                'includeBlankOption' => true,
                                'chosen'             => true,
                                'mandatory'          => true,
                            ],
                        ],
                        'target' => [
                            'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_config']['mergeIdentifierFields']['target'],
                            'inputType'        => 'select',
                            'options_callback' => [\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer::class, 'getTargetFields'],
                            'eval'             => [
                                'groupStyle'         => 'width: 49%',
                                'includeBlankOption' => true,
                                'chosen'             => true,
                                'mandatory'          => true,
                            ],
                        ],
                    ],
                ],
            ],
            'sql'       => "blob NULL",
        ],
        'useCron'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['useCron'],
            'inputType' => 'checkbox',
            'exclude'   => true,
            'eval'      => [
                'tl_class'          => 'w50 clr',
                'submitOnChange'    => true
            ],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'cronInterval'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['cronInterval'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => ['minutely', 'hourly', 'daily', 'weekly', 'monthly'],
            'reference' => &$GLOBALS['TL_LANG']['tl_entity_import_config']['reference']['cronInterval'],
            'eval'      => ['tl_class' => 'w50 clr', 'includeBlankOption' => true, 'mandatory' => true],
            'sql'       => "varchar(12) NOT NULL default ''",
        ],
    ],
];
