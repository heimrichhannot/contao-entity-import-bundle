<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\DataContainer;

use Contao\StringUtil;
use Contao\System;
use Haste\IO\Reader\CsvReader;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class EntityImportSourceContainer
{
    const TYPE_DATABASE = 'db';
    const TYPE_FILE = 'file';

    const SOURCE_TYPE_HTTP = 'http';
    const SOURCE_TYPE_CONTAO_FILE_SYSTEM = 'contao_file_system';
    const SOURCE_TYPE_ABSOLUTE_PATH = 'absolute_path';

    const FILETYPE_CSV = 'csv';
    const FILETYPE_JSON = 'json';

    protected $activeBundles;
    protected $database;
    protected $cache;
    /**
     * @var FileUtil
     */
    private $fileUtil;

    /**
     * @var ModelUtil
     */
    private $modelUtil;

    public function __construct(FileUtil $fileUtil, ModelUtil $modelUtil)
    {
        $this->activeBundles = System::getContainer()->getParameter('kernel.bundles');
        $this->fileUtil = $fileUtil;
        $this->modelUtil = $modelUtil;
    }

    public function initPalette($dc)
    {
        if (null === ($source = $this->modelUtil->findModelInstanceByPk($dc->table, $dc->id))) {
            return;
        }

        $fileType = $source->fileType;

        $delimiter = ('' !== $source->csvDelimiter ? $source->csvDelimiter : ',');
        $enclosure = ('' !== $source->csvEnclosure ? $source->csvEnclosure : '"');
        $escape = ('' !== $source->csvEscape ? $source->csvEscape : '"');

        $arrCsvFields = [];
        $arrOptions = [];

        if ($strSourceFile = $this->fileUtil->getPathFromUuid($source->fileSRC)) {
            $objCsv = new CsvReader($strSourceFile);
            $objCsv->setDelimiter($delimiter);
            $objCsv->setEnclosure($enclosure);
            $objCsv->setEscape($escape);
            $objCsv->rewind();
            $objCsv->next();

            $arrCsvFields = $objCsv->current();
        }

        $dca = &$GLOBALS['TL_DCA'][$dc->table];

        foreach ($arrCsvFields as $index => $field) {
            if ($source->csvHeaderRow) {
                $arrOptions[' '.$index] = $field.' ['.$index.']';
            } else {
                $arrOptions[' '.$index] = '['.$index.']';
            }
        }

        switch ($fileType) {
            case self::FILETYPE_CSV:
                $dca['fields']['fieldMapping']['eval']['multiColumnEditor']['fields']['value']['inputType'] = 'select';
                $dca['fields']['fieldMapping']['eval']['multiColumnEditor']['fields']['value']['options'] = $arrOptions;
                $dca['fields']['fieldMapping']['eval']['multiColumnEditor']['fields']['value']['eval']['includeBlankOption'] = true;
                break;
            case self::FILETYPE_JSON:
                $dca['fields']['fieldMapping']['eval']['multiColumnEditor']['fields']['value']['inputType'] = 'text';
                break;
            default:
                break;
        }

        return $dc;
    }

    public function onLoadFileContent($value, $dc)
    {
        $row = $dc->activeRecord->row();

        switch ($row['sourceType']) {
            case static::SOURCE_TYPE_CONTAO_FILE_SYSTEM:
                if (null === $row['fileSRC'] || null === $row['fileType']) {
                    $value = '';
                    break;
                }
                $value = $this->processInputFile(StringUtil::binToUuid($row['fileSRC']), $row['fileType'], $dc);
                break;
            case static::SOURCE_TYPE_ABSOLUTE_PATH:
                $value = '';
                break;
            case static::SOURCE_TYPE_HTTP:
                if (null === $row['sourceUrl'] || null === $row['fileType']) {
                    $value = '';
                    break;
                }
                $value = $this->processHttp($row['sourceUrl'], $row['fileType'], $dc->id);
                break;
            default:
                $value = '';
                break;
        }

        return $value;
    }

    private function processInputFile($fileUuid, $type, $dc)
    {
        if (null !== $type) {
            $fileType = $type;
        } else {
            $fileType = $this->fileUtil->getFileExtension($this->fileUtil->getPathFromUuid($fileUuid));
        }

        $source = $this->modelUtil->findModelInstanceByPk($dc->table, $dc->id);

        switch ($fileType) {
            case static::FILETYPE_CSV:

                if ($path = $this->fileUtil->getPathFromUuid($fileUuid)) {
                    $delimiter = ('' !== $source->csvDelimiter ? $source->csvDelimiter : ',');
                    $enclosure = ('' !== $source->csvEnclosure ? $source->csvEnclosure : '"');
                    $escape = ('' !== $source->csvEscape ? $source->csvEscape : '"');

                    $objCsv = new CsvReader($path);
                    $objCsv->setDelimiter($delimiter);
                    $objCsv->setEnclosure($enclosure);
                    $objCsv->setEscape($escape);
                    $objCsv->rewind();
                    $objCsv->next();

                    $arrData = $objCsv->current();
                    $fileContent = implode(',', $arrData);
                }
                break;
            case static::FILETYPE_JSON:
                $fileContent = $this->fileUtil->getFileContentFromUuid($fileUuid);
                break;
            default:
                $fileContent = '';
                break;
        }

        return $fileContent;
    }

    private function processHttp($url, $type, $id)
    {
        if (null !== $type) {
            $sourceType = $type;
        } else {
            $sourceType = 'json';
        }

        switch ($sourceType) {
            case static::FILETYPE_CSV:
                $content = 'csv';
                break;
            case static::FILETYPE_JSON:
                $content = file_get_contents($url);
                break;
            default:
                $content = '';
                break;
        }

        return $content;
    }
}
