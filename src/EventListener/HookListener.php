<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\EventListener;

use Haste\IO\Reader\CsvReader;
use HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer;
use HeimrichHannot\EntityImportBundle\Model\EntityImportSourceModel;
use HeimrichHannot\Haste\Util\Files;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;

class HookListener
{
    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function loadDataContainerHook($strTable)
    {
        $source = EntityImportSourceModel::findOneBy('id', $this->request->getGet('id'));
        $fileType = $source->fileType;

        $delimiter = ('' !== $source->csvDelimiter ? $source->csvDelimiter : ',');
        $enclosure = ('' !== $source->csvEnclosure ? $source->csvEnclosure : '"');

        $arrCsvFields = [];
        $arrOptions = [];
        $arrKeys = [];

        if ($strSourceFile = Files::getPathFromUuid($source->fileSRC)) {
            $objCsv = new CsvReader($strSourceFile);
            $objCsv->setDelimiter($delimiter);
            $objCsv->setEnclosure($enclosure);
            $objCsv->rewind();
            $objCsv->next();

            $arrCsvFields = $objCsv->current();
        }

        if (!isset($GLOBALS['TL_DCA'][$strTable])) {
            return;
        }
        $dca = &$GLOBALS['TL_DCA'][$strTable];

        foreach ($arrCsvFields as $index => $field) {
            if ($source->csvHeaderRow) {
                $arrOptions[' '.$index] = $field.' ['.$index.']';
            } else {
                $arrOptions[' '.$index] = '['.$index.']';
            }
        }

        switch ($fileType) {
            case EntityImportSourceContainer::FILETYPE_CSV:
                $dca['fields']['fieldMapping']['eval']['multiColumnEditor']['fields']['value']['inputType'] = 'select';
                $dca['fields']['fieldMapping']['eval']['multiColumnEditor']['fields']['value']['options'] = $arrOptions;
                $dca['fields']['fieldMapping']['eval']['multiColumnEditor']['fields']['value']['eval']['includeBlankOption'] = true;
                break;
            case EntityImportSourceContainer::FILETYPE_JSON:
                $dca['fields']['fieldMapping']['eval']['multiColumnEditor']['fields']['value']['inputType'] = 'text';
                break;
            default:
                break;
        }
    }
}
