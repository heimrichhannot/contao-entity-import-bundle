<?php
/**
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Importer;

interface ImporterSourceInterface extends ImporterInterface
{

    public const ENTITY_IMPORT_CONFIG_TYPE_DATABASE = 'db';
    public const ENTITY_IMPORT_CONFIG_TYPE_FILE = 'file';

    /**
     * @return array
     */
    public function getData();

}