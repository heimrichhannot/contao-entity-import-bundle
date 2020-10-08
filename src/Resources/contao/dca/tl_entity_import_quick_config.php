<?php

System::getContainer()->get('huh.utils.dca')->loadLanguageFile('tl_entity_import_source');
System::getContainer()->get('huh.utils.dca')->loadLanguageFile('tl_entity_import_config');

$GLOBALS['TL_DCA']['tl_entity_import_quick_config'] = [
    'config'   => [
        'dataContainer'     => 'Table',
        'enableVersioning'  => true,
        'onload_callback'   => [
            [\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportQuickConfigContainer::class, 'modifyDca'],
        ],
        'onsubmit_callback' => [
            ['huh.utils.dca', 'setDateAdded'],
        ],
        'oncopy_callback'   => [
            ['huh.utils.dca', 'setDateAddedOnCopy'],
        ],
        'sql'               => [
            'keys' => [
                'id' => 'primary'
            ]
        ]
    ],
    'list'     => [
        'label'             => [
            'fields' => ['title'],
            'format' => '%s'
        ],
        'sorting'           => [
            'mode'         => 1,
            'fields'       => ['title'],
            'headerFields' => ['title'],
            'panelLayout'  => 'filter;sort,search,limit'
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            ],
        ],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_quick_config']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif'
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_quick_config']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif'
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_entity_import_quick_config']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_quick_config']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif'
            ],
            'dryRun' => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_quick_config']['dryRun'],
                'href'  => 'key=dryRun',
                'icon'  => 'important.svg',
            ],
            'import' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_entity_import_quick_config']['import'],
                'href'       => 'key=import',
                'icon'       => 'store.svg',
                'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['tl_entity_import_config']['importConfirm'] . '\')) return false; Backend.getScrollOffset();"',
            ],
        ]
    ],
    'palettes' => [
        'default' => '{general_legend},title,importerConfig;'
    ],
    'fields'   => [
        'id'             => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'tstamp'         => [
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_quick_config']['tstamp'],
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'dateAdded'      => [
            'label'   => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag'    => 6,
            'eval'    => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql'     => "int(10) unsigned NOT NULL default '0'"
        ],
        'title'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_quick_config']['title'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => 1,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'importerConfig' => [
            'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_quick_config']['importerConfig'],
            'exclude'          => true,
            'filter'           => true,
            'inputType'        => 'select',
            'options_callback' => [\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportQuickConfigContainer::class, 'getImporterConfigs'],
            'eval'             => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'submitOnChange' => true, 'chosen' => true],
            'sql'              => "int(10) unsigned NOT NULL default '0'"
        ],
        'fileSRC'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_source']['fileSRC'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => ['filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => true, 'tl_class' => 'w50 clr', 'submitOnChange' => true],
            'sql'       => "binary(16) NULL",
        ],
        'parentEntity'   => [
            'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_quick_config']['parentEntity'],
            'exclude'          => true,
            'filter'           => true,
            'inputType'        => 'select',
            'options_callback' => [\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportQuickConfigContainer::class, 'getParentEntitiesAsOptions'],
            'eval'             => ['tl_class' => 'w50', 'includeBlankOption' => true, 'chosen' => true, 'submitOnChange' => true],
            'sql'              => "int(10) unsigned NOT NULL default '0'"
        ],
        'csvPreviewList' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_quick_config']['csvPreviewList'],
            'exclude'   => true,
            'inputType' => 'listWidget',
            'eval'      => [
                'tl_class'   => 'long clr',
                'listWidget' => [
                    'header_fields_callback' => [\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportQuickConfigContainer::class, 'getHeaderFieldsForPreview'],
                    'items_callback'         => [\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportQuickConfigContainer::class, 'getItemsForPreview']
                ],
            ],
        ],
    ]
];
