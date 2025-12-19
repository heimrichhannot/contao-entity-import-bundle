<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Event;

use Contao\Model;
use Symfony\Contracts\EventDispatcher\Event;

class AfterCsvFileSourceGetRowEvent extends Event
{
    public const NAME = 'huh.entity_import.after_csv_file_source_get_row_event';

    public function __construct(protected array $row, protected Model $sourceModel)
    {
    }

    public function getRow(): array
    {
        return $this->row;
    }

    public function setRow(array $row): void
    {
        $this->row = $row;
    }

    public function getSourceModel(): Model
    {
        return $this->sourceModel;
    }
}
