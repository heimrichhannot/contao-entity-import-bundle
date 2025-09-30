<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

use Contao\File;
use Contao\Message;
use HeimrichHannot\EntityImportBundle\Util\CsvReader;
use HeimrichHannot\EntityImportBundle\Event\AfterCsvFileSourceGetRowEvent;

class CSVFileSource extends AbstractFileSource
{
    public function getMappedData(array $options = []): array
    {
        $data = [];
        $settings = $this->getCsvSettings();
        $file = new File($this->utils->file()->getPathFromUuid($this->sourceModel->fileSRC));

        if (!$file->exists()) {
            return [];
        }

        $csv = new CsvReader($file->path);
        $csv->setDelimiter($settings['delimiter']);
        $csv->setEnclosure($settings['enclosure']);
        $csv->setEscape($settings['escape']);
        $csv->rewind();
        $csv->next();

        while ($current = $csv->current()) {
            $event = $this->eventDispatcher->dispatch(new AfterCsvFileSourceGetRowEvent($current, $this->sourceModel), AfterCsvFileSourceGetRowEvent::NAME);
            $current = $event->getRow();

            if (!$this->sourceModel->csvSkipEmptyLines || [null] !== $current) {
                $data[] = $this->getMappedItemData($current, $this->fieldMapping);
            }

            $csv->next();
        }

        if ($this->sourceModel->csvHeaderRow) {
            array_shift($data);
        }

        return $data;
    }

    public function getHeadingLine(): array
    {
        $settings = $this->getCsvSettings();

        return explode($settings['delimiter'], $this->getLinesFromFile(1));
    }

    protected function getCsvSettings(): array
    {
        try {
            if (null === $this->sourceModel->csvDelimiter) {
                throw new \Exception($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['delimiter']);
            }
            $settings['delimiter'] = $this->sourceModel->csvDelimiter;

            if (null === $this->sourceModel->csvEnclosure) {
                throw new \Exception($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['enclosure']);
            }
            $settings['enclosure'] = $this->sourceModel->csvEnclosure;

            if (null === $this->sourceModel->csvEscape) {
                throw new \Exception($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['escape']);
            }
            $settings['escape'] = $this->sourceModel->csvEscape;
        } catch (\Exception $e) {
            Message::addError(sprintf($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['errorMessage']), $e->getMessage());
        }

        return $settings;
    }
}
