<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

use Contao\Message;
use Haste\IO\Reader\CsvReader;

class CSVFileSource extends AbstractFileSource
{
    public function getMappedData(): array
    {
        $data = [];
        $settings = $this->getCsvSettings();
        $file = $this->fileUtil->getFileFromUuid($this->sourceModel->fileSRC);

        if (null === $file || !$file->exists()) {
            return [];
        }

        $csv = new CsvReader($file->path);
        $csv->setDelimiter($settings['delimiter']);
        $csv->setEnclosure($settings['enclosure']);
        $csv->setEscape($settings['escape']);
        $csv->rewind();
        $csv->next();

        while ($current = $csv->current()) {
            // TODO make configurable?
            $current = array_map('utf8_encode', $current);

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
