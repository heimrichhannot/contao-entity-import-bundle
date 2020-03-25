<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\DataContainer;

use Contao\Database;
use HeimrichHannot\EntityImportBundle\Model\EntityImportSourceModel;

class EntityImportConfigContainer
{
    /**
     * @var Database
     */
    protected $database;

    /**
     * EntityImportConfigContainer constructor.
     */
    public function __construct()
    {
        $this->database = Database::getInstance();
    }

    public function getAllTargetTables($dc)
    {
        return array_values($this->database->listTables(null, true));
    }

    public function getSourceFields($dc)
    {
        $arrOptions = [];

        $fieldMapping = EntityImportSourceModel::findByPk($dc->id)->fieldMapping;

        $arrFieldMapping = unserialize($fieldMapping);

        if (!\is_array($arrFieldMapping) || empty($arrFieldMapping)) {
            return $arrOptions;
        }

        foreach ($arrFieldMapping as $arrField) {
            $arrOptions[$arrField['name']] = $arrField['name'].' ['.$arrField['value'].']';
        }

        return $arrOptions;
    }

    public function getTargetFields($dc)
    {
        $arrOptions = [];
        $arrFields = $this->database->listFields($dc->activeRecord->row()['targetTable']);

        if (!\is_array($arrFields) || empty($arrFields)) {
            return $arrOptions;
        }

        foreach ($arrFields as $arrField) {
            if (\in_array('index', $arrField, true)) {
                continue;
            }

            $arrOptions[$arrField['name']] = $arrField['name'].' ['.$arrField['origtype'].']';
        }

        return $arrOptions;
    }
}
