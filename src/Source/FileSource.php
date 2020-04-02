<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

use Contao\Message;
use Contao\Model;
use Contao\StringUtil;
use GuzzleHttp\Client;
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
        parent::__construct($this->modelUtil);
    }

    public function getSourceModel(): EntityImportSourceModel
    {
        return $this->sourceModel;
    }

    public function setSourceModel(Model $sourceModel)
    {
        $this->sourceModel = $sourceModel;
    }

    public function getFileContent(): string
    {
//        if (null === $path) {
//            Message::addError(sprintf($GLOBALS['TL_LANG']['tl_entity_import_config']['error']['errorMessage'], $GLOBALS['TL_LANG']['tl_entity_import_config']['error']['filePathNotProvided']));
//
//            return false;
//        }

        if (EntityImportSourceContainer::RETRIEVAL_TYPE_CONTAO_FILE_SYSTEM === $this->sourceModel->retrievalType) {
            return file_get_contents($this->sourceModel->filePath);
        } elseif (EntityImportSourceContainer::RETRIEVAL_TYPE_HTTP === $this->sourceModel->retrievalType) {
            $auth = [];

            if (null !== $this->sourceModel->httpAuth) {
                $auth = StringUtil::deserialize($this->sourceModel->httpAuth);
            }

            return $this->getFileFromUrl($this->sourceModel->httpMethod, $this->sourceModel->sourceUrl, $auth)->getBody();
        } elseif (EntityImportSourceContainer::RETRIEVAL_TYPE_ABSOLUTE_PATH === $this->sourceModel->retrievalType) {
            return '';
        }
        $content = '';

//            $event = new

//            return $event->getContent();
        return $content;
    }

    public function getLinesFromFile(int $limit): string
    {
        $fileContent = $this->getFileContent();
        $lines = explode("\n", $fileContent);

        return implode("\n", \array_slice($lines, 0, $limit));
    }

    protected function getFileFromUrl(string $method, string $url, array $auth = [])
    {
        $client = new Client();

        return $client->request($method, $url, $auth);
    }
}
