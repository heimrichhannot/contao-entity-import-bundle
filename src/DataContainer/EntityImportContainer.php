<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\DataContainer;

use Contao\Backend;
use Contao\Database;
use Contao\File;

class EntityImportContainer extends Backend
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

    public function __construct()
    {
        $this->activeBundles = $this->getContainer()->getParameter('kernel.bundles');
        $this->database = Database::getInstance();
        parent::__construct();
    }

    public function onLoadFileSRC($value, $dc)
    {
        $file = \FilesModel::findByUuid($value);

        if ($file) {
            $this->processInputFile($file, null, $dc->id);
        }

        return $value;
    }

    public function onOptionsFileSRC($dc)
    {
        $file = \FilesModel::findByUuid($dc->value);

        if ($file) {
            $this->processInputFile($file, null, $dc->id);
        }

        return $dc;
    }

    public function onLoadFileContent($value, $dc)
    {
        return json_encode(unserialize($value), JSON_PRETTY_PRINT);
    }

    public function onSaveHttpFileType($value, $dc)
    {
        $data = $dc->activeRecord->row();
        $url = $data['sourceUrl'];

        $file = '';
        try {
            $file = new File('files/upload/'.uniqid().$value);
            $file->write(file_get_contents($url));
            $file->close();
        } catch (\Exception $e) {
            /* TODO: write Exception */
        }

        $this->processInputFile($file, $value, $dc->id);

        return $value;
    }

    private function processInputFile($file, $type, $id)
    {
        /* TODO: Restriktionen für Dateigrößen definieren */
        //$fileSize = filesize($file->path);

        if (null !== $type) {
            $fileType = $type;
        } else {
            $fileType = $file->extension;
        }

        switch ($fileType) {
            case static::FILETYPE_CSV:
                $csvFile = fopen($file->path, 'r');
                $csvLine = fgetcsv($csvFile, 0, ',', '"', ';');
                $blob = serialize($csvLine);
                break;
            case static::FILETYPE_JSON:
                $jsonString = file_get_contents($file->path);
                $fileContent = json_decode($jsonString, true);
                $blob = serialize($fileContent[0]);
                break;
            default:
                $blob = null;
                break;
        }

        $this->database->prepare('UPDATE tl_entity_import SET fileContent=? WHERE id=?')->execute($blob, $id);

        return true;
    }
}
