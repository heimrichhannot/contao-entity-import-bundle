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
        $filePath = $this->fileUtil->getPathFromUuid($this->sourceModel->fileSRC);

        $csv = new CsvReader($filePath);
        $csv->setDelimiter($settings['delimiter']);
        $csv->setEnclosure($settings['enclosure']);
        $csv->setEscape($settings['escape']);
        $csv->rewind();
        $csv->next();

        while ($current = $csv->current()) {
            $data[] = $this->getRowData($current, $this->fieldMapping);

            $csv->next();
        }

        if ($this->sourceModel->csvHeaderRow) {
            array_shift($data);
        }

        return $data;
    }

    public function getHeadingLine(): array
    {
        return explode(',', $this->getLinesFromFile(1));
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

    protected function getRowData($current, $mapping): array
    {
        $row = [];

        foreach ($mapping as $element) {
            if ('source_value' === $element['valueType']) {
                $row[$element['name']] = $current[$element['sourceValue']];
            } elseif ('static_value' === $element['valueType']) {
                $row[$element['name']] = $this->stringUtil->replaceInsertTags($element['staticValue']);
            } else {
                continue;
            }
        }

        return $row;
    }
}
