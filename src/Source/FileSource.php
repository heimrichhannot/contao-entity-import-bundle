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

    private $fileUuid;
    /**
     * @var ModelUtil
     */
    private $modelUtil;

    /**
     * FileSource constructor.
     */
    public function __construct(FileUtil $fileUtil, ModelUtil $modelUtil)
    {
        $this->fileUtil = $fileUtil;
        $this->modelUtil = $modelUtil;
    }

    public function getSourceModel(): EntityImportSourceModel
    {
        return $this->sourceModel;
    }

    public function setSourceModel(EntityImportSourceModel $sourceModel)
    {
        $this->sourceModel = $sourceModel;
    }

    public function getFileContent()
    {
        return $this->fileUtil->getFileContentFromUuid($this->fileUuid);
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function setFilePath(int $sourceModel): string
    {
        $source = $this->modelUtil->findModelInstanceByIdOrAlias('tl_entity_import_source', $sourceModel);

        switch ($source->sourceType) {
            case EntityImportSourceContainer::SOURCE_TYPE_HTTP:
                $path = $source->sourceUrl;
                $uuid = null;
                break;
            case EntityImportSourceContainer::SOURCE_TYPE_ABSOLUTE_PATH:
                $path = $source->absolutePath;
                $uuid = null;
                break;
            case EntityImportSourceContainer::SOURCE_TYPE_CONTAO_FILE_SYSTEM:
                $uuid = $source->fileSRC;
                $path = $this->fileUtil->getPathFromUuid($source->fileSRC);
                break;
            default:
                $path = null;
                break;
        }

        if (null === $path) {
            Message::addError(sprintf($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['errorMessage'], $GLOBALS['TL_LANG']['tl_entity_import_config']['error']['filePathNotProvided']));

            return false;
        }

        $this->filePath = $path;
        $this->fileUuid = $uuid;

        return true;
    }
}
