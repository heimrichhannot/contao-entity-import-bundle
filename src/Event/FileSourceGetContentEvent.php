<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Event;

use Contao\Model;
use Symfony\Component\EventDispatcher\Event;

class FileSourceGetContentEvent extends Event
{
    public const NAME = 'huh.entity_import.file_source_get_content_event';

    /**
     * @var string
     */
    private $content;
    /**
     * @var Model
     */
    private $sourceModel;

    /**
     * FileSourceGetContentEvent constructor.
     */
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
}
