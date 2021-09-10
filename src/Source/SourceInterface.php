<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

use Contao\Model;

interface SourceInterface
{
    public function getMapping(): array;

    public function getMappedData(array $options = []): array;

    public function getTotalItemCount(): int;

    public function getSourceModel(): Model;

    public function setSourceModel(Model $sourceModel);

    public function getDomain(): ?string;

    public function setDomain(?string $domain);
}
