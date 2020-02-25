<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Importer;

use HeimrichHannot\EntityImportBundle\Source\SourceInterface;

interface ImporterInterface
{
    public function applySourceMapping(SourceInterface $source): array;

    /**
     * @param bool $dry
     *
     * @return mixed
     */
    public function run($dry = false);
}
