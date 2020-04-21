<?php

$GLOBALS['TL_DCA']['tl_entity_import_config'] = [
    'config'      => [
        'dataContainer'     => 'Table',
        'enableVersioning'  => true,
        'ptable'            => 'tl_entity_import_source',
        'onload_callback'   => [[\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer::class, 'initPalette']],
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
        '__selector__' => ['importMode', 'deleteBeforeImport', 'sortingMode', 'setDateAdded', 'setTstamp', 'generateAlias', 'deletionMode', 'useCron'],
        'default'      => '{general_legend},title,targetTable,importMode;{mapping_legend},fieldMappingCopier,fieldMapping;{fields_legend},setDateAdded,setTstamp,generateAlias;{sorting_legend},sortingMode;{deletion_legend},deleteBeforeImport,deletionMode;{cron_legend},useCron;',
    ],
    'subpalettes' => [
        'importMode_merge'  => 'mergeIdentifierFields',
        'sortingMode_' . \HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer::SORTING_MODE_TARGET_FIELDS
                            => 'targetSortingField,targetSortingOrder,targetSortingContextWhere',
        'deletionMode_' . \HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer::DELETION_MODE_MIRROR =>
            'deletionIdentifierFields,targetDeletionAdditionalWhere',
        'deletionMode_' . \HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer::DELETION_MODE_TARGET_FIELDS =>
            'targetDeletionWhere',
        'setDateAdded'      => 'targetDateAddedField',
        'setTstamp'         => 'targetTstampField',
        'generateAlias'     => 'targetAliasField,aliasFieldPattern',
        'deleteBeforeImport' => 'deleteBeforeImportWhere',
        'useCron'           => 'cronInterval,cronDomain'
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
        'fieldMappingCopier' => [
            'inputType' => 'fieldValueCopier',
            'eval'      => [
                'fieldValueCopier' => [
                    'table'            => 'tl_entity_import_config',
                    'field'            => 'fieldMapping',
                    'config' => [
                        'labelPattern' => '%title% (ID %id%)'
                    ],
                    'options_callback' => ['huh.field_value_copier.util.field_value_copier_util', 'getOptions']
                ]
            ]
        ],
        'fieldMapping'          => [
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
                        'columnName'   => [
                            'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_config']['fieldMapping']['columnName'],
                            'filter'           => true,
                            'exclude'          => true,
                            'inputType'        => 'select',
                            'options'          => [],
                            'options_callback' => [\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer::class, 'getTargetFields'],
                            'eval'             => ['mandatory' => true, 'submitOnChange' => true, 'groupStyle' => 'width: 38%', 'chosen' => true, 'includeBlankOption' => true],
                        ],
                        'valueType'    => [
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
                            'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_config']['fieldMapping']['mappingValue'],
                            'inputType'        => 'select',
                            'options_callback' => [\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer::class, 'getSourceFields'],
                            'eval'             => [
                                'groupStyle'         => 'width: 38%',
                                'mandatory'          => true,
                                'includeBlankOption' => true,
                                'chosen'             => true
                            ],
                        ],
                        'staticValue'  => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['fieldMapping']['staticValue'],
                            'inputType' => 'text',
                            'eval'      => [
                                'groupStyle' => 'width: 38%',
                                'mandatory'  => true
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
        'deleteBeforeImport'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['deleteBeforeImport'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'clr w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'deleteBeforeImportWhere'      => [
            'label'       => &$GLOBALS['TL_LANG']['tl_entity_import_config']['deleteBeforeImportWhere'],
            'inputType'   => 'textarea',
            'exclude'     => true,
            'eval'        => ['class' => 'monospace', 'rte' => 'ace|sql', 'tl_class' => 'long clr', 'decodeEntities' => true],
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
        'sortingMode'           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['sortingMode'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'options'   => \HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer::SORTING_MODES,
            'reference' => &$GLOBALS['TL_LANG']['tl_entity_import_config']['reference']['sortingMode'],
            'eval'      => ['tl_class' => 'w50', 'includeBlankOption' => true, 'submitOnChange' => true],
            'sql'       => "varchar(16) NOT NULL default ''"
        ],
        'targetSortingField'    => [
            'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_config']['targetSortingField'],
            'exclude'          => true,
            'filter'           => true,
            'inputType'        => 'select',
            'options_callback' => [\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer::class, 'getTargetFields'],
            'eval'             => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'chosen' => true],
            'sql'              => "varchar(64) NOT NULL default ''"
        ],
        'targetSortingOrder'      => [
            'label'       => &$GLOBALS['TL_LANG']['tl_entity_import_config']['targetSortingOrder'],
            'inputType'   => 'textarea',
            'exclude'     => true,
            'eval'        => ['class' => 'monospace', 'rte' => 'ace|sql', 'tl_class' => 'w50'],
            'explanation' => 'insertTags',
            'sql'         => "text NULL",
        ],
        'targetSortingContextWhere'      => [
            'label'       => &$GLOBALS['TL_LANG']['tl_entity_import_config']['targetSortingContextWhere'],
            'inputType'   => 'textarea',
            'exclude'     => true,
            'eval'        => ['class' => 'monospace', 'rte' => 'ace|sql', 'tl_class' => 'w50', 'decodeEntities' => true],
            'explanation' => 'insertTags',
            'sql'         => "text NULL",
        ],
        'setDateAdded'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['setDateAdded'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'targetDateAddedField'        => [
            'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_config']['targetDateAddedField'],
            'exclude'          => true,
            'filter'           => true,
            'default'          => 'dateAdded',
            'inputType'        => 'select',
            'options_callback' => [\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer::class, 'getTargetFields'],
            'eval'             => ['tl_class' => 'w50', 'includeBlankOption' => true, 'chosen' => true],
            'sql'              => "varchar(64) NOT NULL default ''"
        ],
        'setTstamp'             => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['setTstamp'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'targetTstampField'           => [
            'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_config']['targetTstampField'],
            'exclude'          => true,
            'filter'           => true,
            'default'          => 'tstamp',
            'inputType'        => 'select',
            'options_callback' => [\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer::class, 'getTargetFields'],
            'eval'             => ['tl_class' => 'w50', 'includeBlankOption' => true, 'chosen' => true],
            'sql'              => "varchar(64) NOT NULL default ''"
        ],
        'generateAlias'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['generateAlias'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'targetAliasField'            => [
            'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_config']['targetAliasField'],
            'exclude'          => true,
            'filter'           => true,
            'default'          => 'alias',
            'inputType'        => 'select',
            'options_callback' => [\HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer::class, 'getTargetFields'],
            'eval'             => ['tl_class' => 'w50', 'includeBlankOption' => true, 'chosen' => true, 'mandatory' => true],
            'sql'              => "varchar(64) NOT NULL default ''"
        ],
        'aliasFieldPattern'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['aliasFieldPattern'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50', 'mandatory' => true],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'useCron'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['useCron'],
            'inputType' => 'checkbox',
            'exclude'   => true,
            'eval'      => [
                'tl_class'       => 'w50 clr',
                'submitOnChange' => true
            ],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'cronInterval'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['cronInterval'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => ['minutely', 'hourly', 'daily', 'weekly', 'monthly'],
            'reference' => &$GLOBALS['TL_LANG']['tl_entity_import_config']['reference']['cronInterval'],
            'eval'      => ['tl_class' => 'w50 clr', 'includeBlankOption' => true, 'mandatory' => true],
            'sql'       => "varchar(12) NOT NULL default ''",
        ],
        'cronDomain' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_entity_import_config']['cronDomain'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => ['maxlength' => 64, 'tl_class' => 'w50', 'mandatory' => true],
            'sql'                     => "varchar(64) NOT NULL default ''"
        ],
        'deletionMode'           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['deletionMode'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'options'   => \HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer::DELETION_MODES,
            'reference' => &$GLOBALS['TL_LANG']['tl_entity_import_config']['reference']['deletionMode'],
            'eval'      => ['tl_class' => 'w50', 'includeBlankOption' => true, 'submitOnChange' => true],
            'sql'       => "varchar(16) NOT NULL default ''"
        ],
        'deletionIdentifierFields' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['deletionIdentifierFields'],
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
        'targetDeletionAdditionalWhere'      => [
            'label'       => &$GLOBALS['TL_LANG']['tl_entity_import_config']['targetDeletionAdditionalWhere'],
            'inputType'   => 'textarea',
            'exclude'     => true,
            'eval'        => ['class' => 'monospace', 'rte' => 'ace|sql', 'tl_class' => 'w50', 'decodeEntities' => true],
            'explanation' => 'insertTags',
            'sql'         => "text NULL",
        ],
        'targetDeletionWhere'      => [
            'label'       => &$GLOBALS['TL_LANG']['tl_entity_import_config']['targetDeletionWhere'],
            'inputType'   => 'textarea',
            'exclude'     => true,
            'eval'        => ['class' => 'monospace', 'rte' => 'ace|sql', 'tl_class' => 'w50', 'decodeEntities' => true],
            'explanation' => 'insertTags',
            'sql'         => "text NULL",
        ],
        'errorNotificationLock' => [
            'sql' => "char(1) NOT NULL default '0'"
        ],
    ],
];
