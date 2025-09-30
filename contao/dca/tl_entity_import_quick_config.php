<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

\Contao\System::loadLanguageFile('tl_entity_import_source');
\Contao\System::loadLanguageFile('tl_entity_import_config');

\HeimrichHannot\UtilsBundle\Dca\DateAddedField::register('tl_entity_import_quick_config');

$GLOBALS['TL_DCA']['tl_entity_import_quick_config'] = [
    'config' => [
        'dataContainer' => \Contao\DC_Table::class,
        'enableVersioning' => true,
        'onload_callback' => [
            [\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportQuickConfigContainer::class, 'modifyDca'],
        ],
        'onsubmit_callback' => [
            [\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportQuickConfigContainer::class, 'cacheCsvRows'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    'list' => [
        'label' => [
            'fields' => ['title'],
            'format' => '%s',
        ],
        'sorting' => [
            'mode' => 1,
            'fields' => ['title'],
            'headerFields' => ['title'],
            'panelLayout' => 'filter;sort,search,limit',
        ],
        'global_operations' => [
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
        ],
        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_quick_config']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_quick_config']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.gif',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_quick_config']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null).'\'))return false;Backend.getScrollOffset()"',
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_quick_config']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
            ],
            'dryRun' => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_quick_config']['dryRun'],
                'href' => 'key=dryRun',
                'icon' => 'important.svg',
                'button_callback' => [\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportQuickConfigContainer::class, 'getDryRunOperation'],
            ],
            'import' => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_quick_config']['import'],
                'href' => 'key=import',
                'icon' => 'theme_import.svg',
                'attributes' => 'data-turbo="false" onclick="if (!confirm(\''.$GLOBALS['TL_LANG']['tl_entity_import_config']['importConfirm'].'\')) return false; Backend.getScrollOffset();"',
            ],
        ],
    ],
    'palettes' => [
        'default' => '{general_legend},explanationImportCouldTakeLong,title,importerConfig;',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_quick_config']['tstamp'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title' => [
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_quick_config']['title'],
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'importerConfig' => [
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_quick_config']['importerConfig'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options_callback' => [\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportQuickConfigContainer::class, 'getImporterConfigs'],
            'eval' => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'submitOnChange' => true, 'chosen' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'fileSRC' => [
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_source']['fileSRC'],
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => ['filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => true, 'tl_class' => 'w50', 'submitOnChange' => true],
            'sql' => 'binary(16) NULL',
        ],
        'parentEntity' => [
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_quick_config']['parentEntity'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options_callback' => [\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportQuickConfigContainer::class, 'getParentEntitiesAsOptions'],
            'eval' => ['tl_class' => 'w50', 'includeBlankOption' => true, 'chosen' => true, 'submitOnChange' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'explanationImportCouldTakeLong' => [
            'inputType' => 'explanation',
            'eval' => [
                'text' => &$GLOBALS['TL_LANG']['tl_entity_import_quick_config']['explanationImportCouldTakeLong'],
                'class' => 'tl_info',
                'tl_class' => 'long clr',
            ],
        ],
        'csvPreviewList' => [
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_quick_config']['csvPreviewList'],
            'exclude' => true,
            'inputType' => 'listWidget',
            'eval' => [
                'tl_class' => 'long clr',
                'listWidget' => [
                    'header_fields_callback' => [\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportQuickConfigContainer::class, 'getHeaderFieldsForPreview'],
                    'items_callback' => [\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportQuickConfigContainer::class, 'getItemsForPreview'],
                ],
            ],
        ],
        'csvHeaderRow' => [
            'label' => &$GLOBALS['TL_LANG']['tl_entity_import_quick_config']['csvHeaderRow'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => [
                'tl_class' => 'w50',
                'submitOnChange' => true,
            ],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'importProgress' => [
            'inputType' => 'huhProgressBar',
            'eval' => [
                'description' => $GLOBALS['TL_LANG']['tl_entity_import_quick_config']['reference']['importProgressDescription'] ?? null,
            ],
        ],
    ],
];
