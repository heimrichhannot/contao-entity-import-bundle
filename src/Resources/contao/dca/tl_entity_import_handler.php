<?php

$GLOBALS['TL_DCA']['tl_entity_import_handler'] = [
    'config' => [
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
    'list' => [
        'sorting'           => [
            'mode'        => 2,
            'fields'      => ['title'],
            'flag'        => 1,
            'panelLayout' => 'search,limit',
        ],
        'label'             => [
            'fields' => ['title']
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
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_handler']['editheader'],
                'href'  => 'act=edit',
                'icon'  => 'header.gif',
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_handler']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_entity_import_handler']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_handler']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
            'import' => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_handler']['import'],
                'href'  => 'key=import',
                'icon'  => version_compare(VERSION, '4.0', '<') ? 'system/modules/devtools/assets/apply.gif' : 'ok.svg',
            ],
        ],
    ],
    'palettes'     => [
        '__selector__' => [],
        'default'      => '{title_legend},title,targetTable,dryRun,mergeTable;',
    ],
    'fields' => [
        'id'                => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'pid'                     => [
            'foreignKey' => 'tl_entity_import_source.title',
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'belongsTo', 'load' => 'eager'],
        ],
        'tstamp'            => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title'             => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_handler']['title'],
            'search'    => true,
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 128, 'tl_class' => 'w50'],
            'sql'       => "varchar(128) NOT NULL default ''",
        ],
        'targetTable'       => [
            'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_handler']['targetTable'],
            'search'           => true,
            'exclude'          => true,
            'inputType'        => 'select',
            'eval'             => ['mandatory' => true, 'submitOnChange' => true, 'tl_class' => 'w50', 'chosen' => true],
            'options_callback' => [\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportHandlerContainer::class, 'getAllTargetTables'],
            'sql'              => "varchar(255) NOT NULL default ''",
        ],
        'dryRun' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_entity_import_handler']['dryRun'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => ['tl_class' => 'w50'],
            'sql'                     => "char(1) NOT NULL default ''"
        ],
        'mergeTable' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_entity_import_handler']['mergeTable'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => ['tl_class' => 'w50'],
            'sql'                     => "char(1) NOT NULL default ''"
        ],
    ]
];