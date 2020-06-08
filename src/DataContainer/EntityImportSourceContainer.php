<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\DataContainer;

use Contao\Database;
use Contao\DataContainer;
use Contao\Message;
use Contao\System;
use HeimrichHannot\EntityImportBundle\Source\AbstractFileSource;
use HeimrichHannot\EntityImportBundle\Source\CSVFileSource;
use HeimrichHannot\EntityImportBundle\Source\SourceFactory;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class EntityImportSourceContainer
{
    const TYPE_DATABASE = 'db';
    const TYPE_FILE = 'file';

    const TYPES = [
        self::TYPE_DATABASE,
        self::TYPE_FILE,
    ];

    const RETRIEVAL_TYPE_HTTP = 'http';
    const RETRIEVAL_TYPE_CONTAO_FILE_SYSTEM = 'contao_file_system';
    const RETRIEVAL_TYPE_ABSOLUTE_PATH = 'absolute_path';

    const RETRIEVAL_TYPES = [
        self::RETRIEVAL_TYPE_HTTP,
        self::RETRIEVAL_TYPE_CONTAO_FILE_SYSTEM,
        self::RETRIEVAL_TYPE_ABSOLUTE_PATH,
    ];

    const FILETYPE_CSV = 'csv';
    const FILETYPE_JSON = 'json';

    const FILETYPES = [
        self::FILETYPE_CSV,
        self::FILETYPE_JSON,
    ];

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
    /**
     * @var DcaUtil
     */
    private $dcaUtil;

    public function __construct(SourceFactory $sourceFactory, FileUtil $fileUtil, ModelUtil $modelUtil, DcaUtil $dcaUtil)
    {
        $this->activeBundles = System::getContainer()->getParameter('kernel.bundles');
        $this->sourceFactory = $sourceFactory;
        $this->fileUtil = $fileUtil;
        $this->modelUtil = $modelUtil;
        $this->dcaUtil = $dcaUtil;
    }

    public function initPalette(?DataContainer $dc)
    {
        if (null === ($sourceModel = $this->modelUtil->findModelInstanceByPk($dc->table, $dc->id))) {
            return;
        }

        $dca = &$GLOBALS['TL_DCA'][$dc->table];

        // database
        switch ($sourceModel->type) {
            case static::TYPE_DATABASE:
                if (!$sourceModel->dbSourceTable) {
                    $dca['palettes'][static::TYPE_DATABASE] = str_replace('fieldMappingCopier', '', $dca['palettes'][static::TYPE_DATABASE]);
                    $dca['palettes'][static::TYPE_DATABASE] = str_replace('fieldMapping', '', $dca['palettes'][static::TYPE_DATABASE]);
                } else {
                    try {
                        $options = array_values(Database::getInstance($sourceModel->row())->getFieldNames($sourceModel->dbSourceTable, true));

                        $sourceValueDca = &$dca['fields']['fieldMapping']['eval']['multiColumnEditor']['fields']['sourceValue'];

                        $sourceValueDca['inputType'] = 'select';
                        $sourceValueDca['options'] = array_combine($options, $options);
                        $sourceValueDca['eval']['includeBlankOption'] = true;
                        $sourceValueDca['eval']['mandatory'] = true;
                        $sourceValueDca['eval']['chosen'] = true;
                    } catch (\Exception $e) {
                    }
                }

                break;

            case static::TYPE_FILE:
                $fileType = $sourceModel->fileType;

                switch ($fileType) {
                    case static::FILETYPE_CSV:

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

                        $sourceValueDca = &$dca['fields']['fieldMapping']['eval']['multiColumnEditor']['fields']['sourceValue'];

                        $sourceValueDca['inputType'] = 'select';
                        $sourceValueDca['options'] = $options;
                        $sourceValueDca['eval']['includeBlankOption'] = true;
                        $sourceValueDca['eval']['mandatory'] = true;
                        $sourceValueDca['eval']['chosen'] = true;
                        $dca['fields']['fileContent']['eval']['rte'] = 'ace';

                        break;

                    case static::FILETYPE_JSON:
                        $dca['fields']['fileContent']['eval']['rte'] = 'ace|json';

                        break;

                    default:
                        break;
                }

                break;
        }
    }

    public function onLoadFileContent(?string $value, ?DataContainer $dc)
    {
        if (null === ($sourceModel = $this->modelUtil->findModelInstanceByPk('tl_entity_import_source', $dc->id))) {
            return '';
        }

        if ($sourceModel->type !== static::TYPE_FILE) {
            return '';
        }

        if (!$sourceModel->fileType) {
            return '';
        }

        /** @var AbstractFileSource $source */
        $source = $this->sourceFactory->createInstance($dc->id);

        if ($sourceModel->fileType === static::FILETYPE_CSV) {
            return $source->getLinesFromFile(25, true)."\n...";
        }

        if ($sourceModel->fileType === static::FILETYPE_JSON) {
            $string = json_decode($source->getFileContent(false));

            return json_encode($string, JSON_PRETTY_PRINT);
        }

        return $source->getFileContent(true);
    }

    public function getAllTargetTables(?DataContainer $dc): array
    {
        if (null === ($source = $this->modelUtil->findModelInstanceByPk('tl_entity_import_source', $dc->id))) {
            return [];
        }

        try {
            $options = array_values(Database::getInstance($source->row())->listTables(null, true));
        } catch (\Exception $e) {
            Message::addError(sprintf($GLOBALS['TL_LANG']['MSC']['entityImport']['dbConnectionError'], $e->getMessage()));

            return [];
        }

        return $options;
    }
}
