<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\DataContainer;

use Contao\Database;

class EntityImportHandlerContainer
{
    /**
     * @var Database
     */
    protected $database;

    /**
     * EntityImportHandlerContainer constructor.
     */
    public function __construct()
    {
        $this->database = Database::getInstance();
    }

    public function getAllTargetTables($dc)
    {
        return array_values($this->database->listTables(null, true));
    }
}
