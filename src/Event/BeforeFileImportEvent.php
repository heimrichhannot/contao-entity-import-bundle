<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Event;

use Contao\Model;
use HeimrichHannot\EntityImportBundle\Source\SourceInterface;
use Symfony\Component\EventDispatcher\Event;

class BeforeFileImportEvent extends Event
{
    public const NAME = 'huh.entity_import.before_file_import_event';

    protected string $path;
    protected $content;
    protected array $mappedItem;
    protected array $item;
    protected Model $configModel;
    protected SourceInterface $source;
    protected bool $dryRun;

    public function __construct(?string $path, $content, array $mappedItem, array $item, Model $configModel, SourceInterface $source, bool $dryRun = false)
    {
        $this->path = $path;
        $this->content = $content;
        $this->mappedItem = $mappedItem;
        $this->item = $item;
        $this->configModel = $configModel;
        $this->source = $source;
        $this->dryRun = $dryRun;
    }

    public function getConfigModel(): Model
    {
        return $this->configModel;
    }

    public function setConfigModel(Model $configModel): void
    {
        $this->configModel = $configModel;
    }

    public function getSource(): SourceInterface
    {
        return $this->source;
    }

    public function setSource(SourceInterface $source): void
    {
        $this->source = $source;
    }

    public function isDryRun(): bool
    {
        return $this->dryRun;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function getMappedItem(): array
    {
        return $this->mappedItem;
    }

    public function setMappedItem(array $mappedItem): void
    {
        $this->mappedItem = $mappedItem;
    }

    public function getItem(): array
    {
        return $this->item;
    }

    public function setItem(array $item): void
    {
        $this->item = $item;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content): void
    {
        $this->content = $content;
    }
}
