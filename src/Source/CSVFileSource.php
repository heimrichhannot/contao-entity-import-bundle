<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

class CSVFileSource extends FileSource
{
    public function getData(): array
    {
        $sourceModel = $this->sourceModel;

        $fieldMapping = unserialize($this->fieldMapping);

        /*        if ($strSourceFile = Files::getPathFromUuid($this->sourceFile))
                {
                    $objCsv = new CsvReader($strSourceFile);
                    $objCsv->setDelimiter($this->delimiter);
                    $objCsv->setEnclosure($this->enclosure);
                    $objCsv->rewind();
                    $objCsv->next();

                    while ($arrCurrent = $objCsv->current())
                    {
                        $this->arrItems[] = $arrCurrent;
                        $objCsv->next();
                    }
                }*/

        $arrData = [];

        return $arrData;
    }
}
