<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Event;

use Contao\Model;
use Symfony\Component\EventDispatcher\Event;

class AfterCsvFileSourceGetRowEvent extends Event
{
    public const NAME = 'huh.entity_import.after_csv_file_source_get_row_event';

    /**
     * @var array
     */
    protected $row;

    /**
     * @var Model
     */
    protected $sourceModel;

    public function __construct(array $row, Model $sourceModel)
    {
        $this->row = $row;
        $this->sourceModel = $sourceModel;
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
