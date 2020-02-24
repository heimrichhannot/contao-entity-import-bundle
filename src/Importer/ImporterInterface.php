<?php
/**
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Importer;

interface ImporterInterface
{

    /**
     * @param bool $dry
     *
     * @return mixed
     */
    public function run($dry = false);

}