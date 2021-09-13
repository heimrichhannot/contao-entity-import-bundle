<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Importer;

use Symfony\Component\Console\Style\SymfonyStyle;

interface ImporterInterface
{
    const LOCK_KEY = 'contao_entity_import_bundle.%s.id%s';
    const LOCK_DIRECTORY = '/var';

    public function getDataFromSource(): array;

    public function run(): array;

    public function setDryRun(bool $dry): void;

    public function setInputOutput(SymfonyStyle $io): void;

    public function outputResultMessages(array $result): void;
}
