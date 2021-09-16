<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Event;

use Contao\Model;
use Symfony\Contracts\EventDispatcher\Event;

class AfterFileSourceGetContentEvent extends Event
{
    public const NAME = 'huh.entity_import.after_file_source_get_content_event';

    protected string $content = '';
    protected Model $sourceModel;

    public function __construct(string $content, Model $sourceModel)
    {
        $this->content = $content;
        $this->sourceModel = $sourceModel;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content)
    {
        $this->content = $content;
    }

    public function getSourceModel(): Model
    {
        return $this->sourceModel;
    }

    public function setSourceModel(Model $sourceModel): void
    {
        $this->sourceModel = $sourceModel;
    }
}
