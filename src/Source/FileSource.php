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
use HeimrichHannot\UtilsBundle\Request\CurlRequestUtil;

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
     * @var CurlRequestUtil
     */
    private $curlRequestUtil;

    /**
     * FileSource constructor.
     */
    public function __construct(FileUtil $fileUtil, ModelUtil $modelUtil, CurlRequestUtil $curlRequestUtil)
    {
        $this->fileUtil = $fileUtil;
        $this->modelUtil = $modelUtil;
        $this->curlRequestUtil = $curlRequestUtil;
    }

    public function getSourceModel(): EntityImportSourceModel
    {
        return $this->sourceModel;
    }

    public function setSourceModel(EntityImportSourceModel $sourceModel)
    {
        $this->sourceModel = $sourceModel;
    }

    public function getFileContent(): string
    {
        return file_get_contents($this->filePath);
    }

    public function getLinesFromFile(int $limit): string
    {
        $fileContent = $this->getFileContent();
        $lines = explode("\n", $fileContent);

        return implode("\n", \array_slice($lines, 0, $limit));
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function setFilePath(int $sourceModel): string
    {
        $source = $this->modelUtil->findModelInstanceByIdOrAlias('tl_entity_import_source', $sourceModel);

        switch ($source->retrievalType) {
            case EntityImportSourceContainer::RETRIEVAL_TYPE_HTTP:
                try {
                } catch (\Exception $e) {
                }

                $handle = $this->curlRequestUtil->createCurlHandle($source->sourceUrl);
                $handle->execute();
                $response = $handle->getInfo(CURLINFO_HTTP_CODE);
                $handle->close();

                if (200 == $response) {
                    $path = $source->sourceUrl;
                } else {
                    //TODO: check for cached file
                    $path = '';
                }

                break;

            case EntityImportSourceContainer::RETRIEVAL_TYPE_ABSOLUTE_PATH:
                $path = $source->absolutePath;

                break;

            case EntityImportSourceContainer::RETRIEVAL_TYPE_CONTAO_FILE_SYSTEM:
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

        return true;
    }
}
