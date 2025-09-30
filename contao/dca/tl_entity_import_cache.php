<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$GLOBALS['TL_DCA']['tl_entity_import_cache'] = [
    'config' => [
        'dataContainer' => \Contao\DC_Table::class,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'cache_pid,cache_ptable' => 'index',
            ],
        ],
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'cache_ptable' => [
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        'cache_pid' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
    ],
];
