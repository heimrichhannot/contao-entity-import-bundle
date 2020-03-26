<?php

$GLOBALS['TL_DCA']['tl_entity_import_config'] = [
    'config'      => [
        'dataContainer'    => 'Table',
        'enableVersioning' => true,
        'ptable'           => 'tl_entity_import_source',
        'sql'              => [
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
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_config']['editheader'],
                'href'  => 'act=edit',
                'icon'  => 'header.gif',
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
                'icon'  => 'regular.svg',
            ],
            'import'     => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_config']['import'],
                'href'  => 'key=import',
                'icon'  => 'ok.svg',
                'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['tl_entity_import_config']['importConfirm'] . '\')) return false; Backend.getScrollOffset();"',
            ],
        ],
    ],
    'palettes'    => [
        '__selector__' => ['mergeTable'],
        'default'      => '{title_legend},title,targetTable,mergeTable;',
    ],
    'subpalettes' => [
        'mergeTable' => 'mergeIdentifierFields',
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
            'eval'             => ['mandatory' => true, 'submitOnChange' => true, 'tl_class' => 'w50', 'chosen' => true],
            'options_callback' => [\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer::class, 'getAllTargetTables'],
            'sql'              => "varchar(255) NOT NULL default ''",
        ],
        'mergeTable'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['mergeTable'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
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
                                'groupStyle'         => 'width: 450px',
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
                                'groupStyle'         => 'width:450px',
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
    ],
];