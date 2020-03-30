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

    public function __construct(FileUtil $fileUtil)
    {
        $this->activeBundles = System::getContainer()->getParameter('kernel.bundles');
        $this->fileUtil = $fileUtil;
    }

    public function onLoadFileSRC($value, $dc)
    {
        $file = System::getContainer()->get('huh.utils.model')->callModelMethod('tl_files', 'findByUuid', $value);

        if ($file) {
            $this->processInputFile($file, null, $dc);
        }

        return $value;
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

        switch ($fileType) {
            case static::FILETYPE_CSV:

                if ($path = $this->fileUtil->getPathFromUuid($fileUuid)) {
                    $objCsv = new CsvReader($path);
                    $csvDelimiter = '' !== $dc->csvDelimiter ? $dc->csvDelimiter : ',';
                    $csvEnclosure = '' !== $dc->csvEscape ? $dc->csvEscape : '"';
                    $csvEscape = '' !== $dc->csvEscape ? $dc->csvEscape : ';';

                    $objCsv->setDelimiter($csvDelimiter);
                    $objCsv->setEnclosure($csvEnclosure);
                    $objCsv->setEscape($csvEscape);
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
