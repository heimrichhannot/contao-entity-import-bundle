<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

use Contao\Model;
use GuzzleHttp\Client;
use HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer;
use HeimrichHannot\EntityImportBundle\Event\AfterFileSourceGetContentEvent;
use HeimrichHannot\EntityImportBundle\Event\BeforeAuthenticationEvent;
use HeimrichHannot\EntityImportBundle\Model\EntityImportSourceModel;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\String\StringUtil;
use Symfony\Component\EventDispatcher\EventDispatcher;

abstract class AbstractFileSource extends AbstractSource
{
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
    protected $modelUtil;

    /**
     * @var StringUtil
     */
    protected $stringUtil;
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * AbstractFileSource constructor.
     */
    public function __construct(FileUtil $fileUtil, ModelUtil $modelUtil, StringUtil $stringUtil, EventDispatcher $eventDispatcher)
    {
        $this->fileUtil = $fileUtil;
        $this->modelUtil = $modelUtil;
        $this->stringUtil = $stringUtil;
        $this->eventDispatcher = $eventDispatcher;
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

    public function getLinesFromFile(int $limit): string
    {
        $fileContent = $this->getFileContent();
        $lines = explode("\n", $fileContent);

        return implode("\n", \array_slice($lines, 0, $limit));
    }

    public function getFileContent(): string
    {
        $content = '';

        switch ($this->sourceModel->retrievalType) {
            case EntityImportSourceContainer::RETRIEVAL_TYPE_CONTAO_FILE_SYSTEM:
                $content = file_get_contents($this->fileUtil->getPathFromUuid($this->sourceModel->fileSRC));

                break;

            case EntityImportSourceContainer::RETRIEVAL_TYPE_HTTP:
                $auth = [];

                if (null !== $this->sourceModel->httpAuth) {
                    $httpAuth = \Contao\StringUtil::deserialize($this->sourceModel->httpAuth);
                    $auth = ['auth' => [$httpAuth['username'], $httpAuth['password']]];
                }

                $event = $this->eventDispatcher->dispatch(BeforeAuthenticationEvent::NAME, new BeforeAuthenticationEvent($auth, $this->sourceModel));

                $httpResponse = $this->getFileFromUrl($this->sourceModel->httpMethod, $this->sourceModel->sourceUrl, $event->getAuth());

                if (200 === $httpResponse->getStatusCode()) {
                    $content = $httpResponse->getBody();
                    $this->setFileCache($content);
                } else {
                    //TODO: prevent null if file dont exist in cache
                    $content = $this->getFileFromCache($this->sourceModel->sourceUrl);
                }

                break;

            case EntityImportSourceContainer::RETRIEVAL_TYPE_ABSOLUTE_PATH:
                $content = file_get_contents($this->sourceModel->absolutePath);

                break;
        }

        $event = $this->eventDispatcher->dispatch(AfterFileSourceGetContentEvent::NAME, new AfterFileSourceGetContentEvent($content, $this->sourceModel));
        $content = $event->getContent();

        return $content;
    }

    protected function getFileFromUrl(string $method, string $url, array $auth = [])
    {
        $client = new Client();

        return $client->request($method, $url, $auth);
    }

    protected function setFileCache($content)
    {
        //TODO -> logic to cache files
    }

    protected function getFileFromCache(string $fileIdentifier): string
    {
        //TODO -> logic to get cached files content
        return '';
    }
}
