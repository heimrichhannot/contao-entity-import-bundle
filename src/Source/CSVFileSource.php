<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

use Contao\Message;
use HeimrichHannot\EntityImportBundle\Event\AfterCsvFileSourceGetRowEvent;
use League\Csv\Reader;

class CSVFileSource extends AbstractFileSource
{
    public function getMappedData(array $options = []): array
    {
        $data = [];
        $settings = $this->getCsvSettings();
        $file = $this->fileUtil->getFileFromUuid($this->sourceModel->fileSRC);

        if (null === $file || !$file->exists()) {
            return [];
        }

        $csv = Reader::createFromPath($file->path);
        $csv->setDelimiter($settings['delimiter']);
        $csv->setEnclosure($settings['enclosure']);
        $csv->setEscape($settings['escape']);

        $records = $csv->getRecords();

        foreach ($records as $offset => $record) {
            $event = $this->eventDispatcher->dispatch(new AfterCsvFileSourceGetRowEvent($record, $this->sourceModel), AfterCsvFileSourceGetRowEvent::NAME);

            if (!$this->sourceModel->csvSkipEmptyLines || [null] !== $record) {
                $data[] = $this->getMappedItemData($record, $this->fieldMapping);
            }
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
            Message::addError($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['errorMessage'], $e->getMessage());
        }

        return $settings;
    }
}
