<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\DataContainer;

use Contao\StringUtil;
use Contao\System;
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
        $file = \FilesModel::findByUuid($value);

        if ($file) {
            $this->processInputFile($file, null, $dc->id);
        }

        return $value;
    }

    public function onLoadFileContent($value, $dc)
    {
        $row = $dc->activeRecord->row();

        if (null !== $row['fileSRC']) {
            switch ($row['sourceType']) {
                case static::SOURCE_TYPE_CONTAO_FILE_SYSTEM:
                    $value = $this->processInputFile(StringUtil::binToUuid($row['fileSRC']), $row['fileType'], $dc->id);
                    break;
                case static::SOURCE_TYPE_ABSOLUTE_PATH:
                    break;
                case static::SOURCE_TYPE_HTTP:
                    break;
                default:
                    break;
            }
        }

        return $value; //json_encode($value, JSON_PRETTY_PRINT);
    }

    public function onSaveHttpFileType($value, $dc)
    {
        $data = $dc->activeRecord->row();
        $url = $data['sourceUrl'];

//            $file = new File('files/upload/'.uniqid().".".$value);
//            $file->write(file_get_contents($url));
//            $file->close();

        try {
//            $cacheItem = $this->cache->getItem('dsjfijsdfo');
        } catch (\Exception $e) {
            /* TODO: write Exception */
        }

//        if(!is_null($file)){
//            $this->processInputFile($file, $value, $dc->id);
//        }

        return $value;
    }

    private function processInputFile($fileUuid, $type, $id)
    {
        /* TODO: Restriktionen für Dateigrößen definieren */
        //$fileSize = filesize($file->path);

        if (null !== $type) {
            $fileType = $type;
        } else {
            $fileType = $this->fileUtil->getFileExtension($this->fileUtil->getPathFromUuid($fileUuid));
        }

        $path = $this->fileUtil->getPathFromUuid($fileUuid);

        switch ($fileType) {
            case static::FILETYPE_CSV:
                $csvFile = fopen($path, 'r');
                $fileContentArray = fgetcsv($csvFile, 0, ',', '"', ';');
                $fileContent = implode(',', $fileContentArray);
                break;
            case static::FILETYPE_JSON:
                $fileContent = file_get_contents($path);
                break;
            default:
                $fileContent = '';
                break;
        }

        return $fileContent;
    }
}
