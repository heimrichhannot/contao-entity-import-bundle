<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

use Contao\Message;
use HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer;
use HeimrichHannot\EntityImportBundle\Model\EntityImportSourceModel;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\Config\FileLocator;

abstract class FileSource extends Source
{
    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var FileUtil
     */
    protected $fileUtil;

    /**
     * @var EntityImportSourceModel
     */
    protected $sourceModel;
    /**
     * @var ModelUtil
     */
    private $modelUtil;

    /**
     * FileSource constructor.
     *
     * @param FileLocator $fileLocator
     */
    public function __construct(FileUtil $fileUtil, ModelUtil $modelUtil)
    {
        $this->fileUtil = $fileUtil;
        $this->modelUtil = $modelUtil;
    }

    public function getFileContent()
    {
        $fileUuid = $this->modelUtil->callModelMethod('tl_files', 'findByPath', $this->getFilePath());

        return $this->fileUtil->getFileContentFromUuid($fileUuid);
    }

    public function getFilePath(): string
    {
        $source = $this->sourceModel;

        switch ($source->sourceType) {
            case EntityImportSourceContainer::SOURCE_TYPE_HTTP:
                $path = $source->sourceUrl;
                break;
            case EntityImportSourceContainer::SOURCE_TYPE_ABSOLUTE_PATH:
                $path = $source->absolutePath;
                break;
            case EntityImportSourceContainer::SOURCE_TYPE_CONTAO_FILE_SYSTEM:
                $path = $source->fileSRC;
                break;
            default:
                $path = null;
                break;
        }

        if (null === $path) {
            Message::addError(sprintf($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['errorMessage']), $GLOBALS['TL_LANG']['tl_entity_import_config']['error']['filePathNotProvided']);

            return false;
        }

        $this->filePath = $path;

        return true;
    }
}
