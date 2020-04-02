<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\DataContainer;

use Contao\System;
use Haste\IO\Reader\CsvReader;
use HeimrichHannot\EntityImportBundle\Source\CSVFileSource;
use HeimrichHannot\EntityImportBundle\Source\FileSource;
use HeimrichHannot\EntityImportBundle\Source\SourceFactory;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class EntityImportSourceContainer
{
    const TYPE_DATABASE = 'db';
    const TYPE_FILE = 'file';

    const RETRIEVAL_TYPE_HTTP = 'http';
    const RETRIEVAL_TYPE_CONTAO_FILE_SYSTEM = 'contao_file_system';
    const RETRIEVAL_TYPE_ABSOLUTE_PATH = 'absolute_path';

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
    /**
     * @var SourceFactory
     */
    private $sourceFactory;

    public function __construct(FileUtil $fileUtil, ModelUtil $modelUtil, SourceFactory $sourceFactory)
    {
        $this->activeBundles = System::getContainer()->getParameter('kernel.bundles');
        $this->fileUtil = $fileUtil;
        $this->modelUtil = $modelUtil;
        $this->sourceFactory = $sourceFactory;
    }

    public function initPalette($dc)
    {
        if (null === ($sourceModel = $this->modelUtil->findModelInstanceByPk($dc->table, $dc->id))) {
            return;
        }

        $fileType = $sourceModel->fileType;

        $dca = &$GLOBALS['TL_DCA'][$dc->table];

        switch ($fileType) {
            case self::FILETYPE_CSV:

                /** @var CSVFileSource $source */
                $source = $this->sourceFactory->createInstance($sourceModel->id);

                $options = [];
                $fields = $source->getHeadingLine();

                foreach ($fields as $index => $field) {
                    if ($sourceModel->csvHeaderRow) {
                        $options[' '.$index] = $field.' ['.$index.']';
                    } else {
                        $options[' '.$index] = '['.$index.']';
                    }
                }

                $dca['fields']['fieldMapping']['eval']['multiColumnEditor']['fields']['sourceValue']['inputType'] = 'select';
                $dca['fields']['fieldMapping']['eval']['multiColumnEditor']['fields']['sourceValue']['options'] = $options;
                $dca['fields']['fieldMapping']['eval']['multiColumnEditor']['fields']['sourceValue']['eval']['includeBlankOption'] = true;

                break;

            case self::FILETYPE_JSON:

                $dca['fields']['fieldMapping']['eval']['multiColumnEditor']['fields']['sourceValue']['inputType'] = 'text';

                break;

            default:
                break;
        }

        return $dc;
    }

    public function onLoadFileContent($value, $dc)
    {
        if (null === ($sourceModel = $this->modelUtil->findModelInstanceByPk('tl_entity_import_source', $dc->id))) {
            return '';
        }

        if ($sourceModel->type !== static::TYPE_FILE) {
            return '';
        }

        /** @var FileSource $source */
        $source = $this->sourceFactory->createInstance($dc->id);

        if ($sourceModel->fileType === static::FILETYPE_CSV) {
            return $source->getLinesFromFile(5);
        }

        return $source->getFileContent();
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
                    $delimiter = ($source->csvDelimiter ?: ',');
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
