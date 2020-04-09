<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\DataContainer;

use Contao\DataContainer;
use Contao\System;
use HeimrichHannot\EntityImportBundle\Source\AbstractFileSource;
use HeimrichHannot\EntityImportBundle\Source\CSVFileSource;
use HeimrichHannot\EntityImportBundle\Source\SourceFactory;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class EntityImportSourceContainer
{
    const TYPE_DATABASE = 'db';
    const TYPE_FILE = 'file';

    const TYPES = [
//        self::TYPE_DATABASE,
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

    public function __construct(FileUtil $fileUtil, ModelUtil $modelUtil, SourceFactory $sourceFactory)
    {
        $this->activeBundles = System::getContainer()->getParameter('kernel.bundles');
        $this->fileUtil = $fileUtil;
        $this->modelUtil = $modelUtil;
        $this->sourceFactory = $sourceFactory;
    }

    public function initPalette(?DataContainer $dc)
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

                $sourceValueDca = &$dca['fields']['fieldMapping']['eval']['multiColumnEditor']['fields']['sourceValue'];

                $sourceValueDca['inputType'] = 'select';
                $sourceValueDca['options'] = $options;
                $sourceValueDca['eval']['includeBlankOption'] = true;
                $sourceValueDca['eval']['mandatory'] = true;
                $sourceValueDca['eval']['chosen'] = true;
                $dca['fields']['fileContent']['eval']['rte'] = 'ace';

                break;

            case self::FILETYPE_JSON:
                $dca['fields']['fileContent']['eval']['rte'] = 'ace|json';

                break;

            default:
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

        /** @var AbstractFileSource $source */
        $source = $this->sourceFactory->createInstance($dc->id);

        if ($sourceModel->fileType === static::FILETYPE_CSV) {
            return $source->getLinesFromFile(5, true);
        }

        if ($sourceModel->fileType === static::FILETYPE_JSON) {
            $string = json_decode($source->getFileContent(false));

            return json_encode($string,JSON_PRETTY_PRINT);
        }

        return $source->getFileContent(true);
    }
}
