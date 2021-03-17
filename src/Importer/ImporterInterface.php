<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Importer;

interface ImporterInterface
{
    const LOCK_KEY = 'contao_entity_import_bundle.%s.id%s';
    const LOCK_DIRECTORY = '/var';

    public function getDataFromSource(): array;

    public function run(): bool;

    public function setDryRun(bool $dry);
}
