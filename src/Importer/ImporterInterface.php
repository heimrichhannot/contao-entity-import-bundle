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
    const MESSAGE_TYPE_SUCCESS = 'success';
    const MESSAGE_TYPE_ERROR = 'error';
    const MESSAGE_TYPE_WARNING = 'warning';

    public function getDataFromSource(): array;

    public function run(): array;

    public function setDryRun(bool $dry): void;

    public function setWebCronMode(bool $dry): void;

    public function setInputOutput(SymfonyStyle $io): void;

    public function outputResultMessage(string $message, string $type): void;

    public function outputFinalResultMessage(array $result): void;
}
