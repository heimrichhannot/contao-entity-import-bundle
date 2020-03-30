<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

use Contao\Message;
use Haste\IO\Reader\CsvReader;
use HeimrichHannot\Haste\Util\Files;

class CSVFileSource extends FileSource
{
    public function getMappedData(): array
    {
        $sourceModel = $this->sourceModel;
        $fieldMapping = unserialize($this->fieldMapping);
        $arrData = [];

        try {
            $delimiter = $sourceModel->csvDelimiter;
            if (null === $delimiter) {
                throw new \Exception($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['delimiter']);
            }
            $enclosure = $sourceModel->csvEnclosure;
            if (null === $enclosure) {
                throw new \Exception($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['enclosure']);
            }

            $escape = $sourceModel->csvEscape;
            if (null === $escape) {
                throw new \Exception($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['escape']);
            }
        } catch (\Exception $e) {
            Message::addError(sprintf($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['errorMessage']), $e->getMessage());
        }

        if ($strSourceFile = Files::getPathFromUuid($sourceModel->fileSRC)) {
            $objCsv = new CsvReader($strSourceFile);
            $objCsv->setDelimiter($delimiter);
            $objCsv->setEnclosure($enclosure);
            $objCsv->setEscape($escape);
            $objCsv->rewind();
            $objCsv->next();

            while ($arrCurrent = $objCsv->current()) {
                $arrData[] = $this->getRowData($arrCurrent, $fieldMapping);

                $objCsv->next();
            }
        }

        if ($sourceModel->csvHeaderRow) {
            array_shift($arrData);
        }

        return $arrData;
    }

    protected function getRowData($arrCurrent, $arrMapping): array
    {
        $arrElement = [];
        foreach ($arrMapping as $mappingElement) {
            $arrElement[$mappingElement['name']] = $arrCurrent[$mappingElement['value']];
        }

        return $arrElement;
    }
}
