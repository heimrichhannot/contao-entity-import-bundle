<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

use Contao\Message;
use Haste\IO\Reader\CsvReader;
use HeimrichHannot\EntityImportBundle\Event\AfterCsvFileSourceGetRowEvent;

class CSVFileSource extends AbstractFileSource
{
    public function getTotalItemCount(): int
    {
        $count = 0;
        $settings = $this->getCsvSettings();
        $file = $this->fileUtil->getFileFromUuid($this->sourceModel->fileSRC);

        if (null === $file || !$file->exists()) {
            return 0;
        }

        $csv = new CsvReader($file->path);
        $csv->setDelimiter($settings['delimiter']);
        $csv->setEnclosure($settings['enclosure']);
        $csv->setEscape($settings['escape']);
        $csv->rewind();
        $csv->next();

        while ($csv->current()) {
            ++$count;
            $csv->next();
        }

        if ($this->sourceModel->csvHeaderRow) {
            --$count;
        }

        return $count;
    }

    public function getMappedData(array $options = []): array
    {
        $data = [];
        $settings = $this->getCsvSettings();
        $file = $this->fileUtil->getFileFromUuid($this->sourceModel->fileSRC);

        $limit = $options['itemLimit'] ?? 0;
        $offset = $options['itemOffset'] ?? 0;
        $processInChunks = $limit > 0;

        if (null === $file || !$file->exists()) {
            return [];
        }

        $csv = new CsvReader($file->path);
        $csv->setDelimiter($settings['delimiter']);
        $csv->setEnclosure($settings['enclosure']);
        $csv->setEscape($settings['escape']);
        $csv->rewind();
        $csv->next();

        $i = 0;

        while ($current = $csv->current()) {
            if ($processInChunks && $i > $limit + $offset - 1) {
                break;
            }

            if ($processInChunks && $i < $offset) {
                ++$i;
                $csv->next();

                continue;
            }

            $event = $this->eventDispatcher->dispatch(AfterCsvFileSourceGetRowEvent::NAME, new AfterCsvFileSourceGetRowEvent($current, $this->sourceModel));
            $current = $event->getRow();

            if (!$this->sourceModel->csvSkipEmptyLines || [null] !== $current) {
                $data[] = $this->getMappedItemData($current, $this->fieldMapping);
            }

            if ($processInChunks) {
                ++$i;
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
