<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

use Ausi\SlugGenerator\SlugGenerator;
use Contao\Model;
use GuzzleHttp\Client;
use HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer;
use HeimrichHannot\EntityImportBundle\Event\AfterFileSourceGetContentEvent;
use HeimrichHannot\EntityImportBundle\Event\BeforeAuthenticationEvent;
use HeimrichHannot\EntityImportBundle\Model\EntityImportSourceModel;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\String\StringUtil;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var FilesystemCache
     */
    private $filesystemCache;

    /**
     * @var ContainerUtil
     */
    private $containerUtil;

    /**
     * AbstractFileSource constructor.
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, FileUtil $fileUtil, ModelUtil $modelUtil, StringUtil $stringUtil, ContainerUtil $containerUtil)
    {
        $this->fileUtil = $fileUtil;
        $this->modelUtil = $modelUtil;
        $this->stringUtil = $stringUtil;
        $this->containerUtil = $containerUtil;
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

    public function getFilesystemCache(): FilesystemCache
    {
        if (null === $this->filesystemCache) {
            $this->filesystemCache = new FilesystemCache('contaoEntityImportBundle', 300);
        }

        return $this->filesystemCache;
    }

    public function getLinesFromFile(int $limit, bool $cache = false): string
    {
        $fileContent = $this->getFileContent($cache);
        $lines = explode("\n", $fileContent);

        return implode("\n", \array_slice($lines, 0, $limit));
    }

    public function getFileContent(bool $cache = false): string
    {
        $content = '';
        $projectDir = $this->containerUtil->getProjectDir();

        switch ($this->sourceModel->retrievalType) {
            case EntityImportSourceContainer::RETRIEVAL_TYPE_CONTAO_FILE_SYSTEM:
                $path = $projectDir.'/'.$this->fileUtil->getPathFromUuid($this->sourceModel->fileSRC);

                if (file_exists($path)) {
                    $content = file_get_contents($path);
                }

                break;

            case EntityImportSourceContainer::RETRIEVAL_TYPE_HTTP:
                $auth = [];

                if (null !== $this->sourceModel->httpAuth) {
                    $httpAuth = \Contao\StringUtil::deserialize($this->sourceModel->httpAuth);
                    $auth = ['auth' => [$httpAuth['username'], $httpAuth['password']]];
                }

                $event = $this->eventDispatcher->dispatch(BeforeAuthenticationEvent::NAME, new BeforeAuthenticationEvent($auth, $this->sourceModel));

                if ($cache) {
                    $generator = new SlugGenerator();
                    $cacheKey = $generator->generate($this->sourceModel->sourceUrl);
                    $content = $this->getFileCache($cacheKey);

                    if (empty($content)) {
                        $this->setFileCache($this->sourceModel->sourceUrl, $this->sourceModel->httpMethod, $event->getAuth(), $cacheKey);
                        $content = $this->getFileCache($cacheKey);
                    }

                    break;
                }

                $httpResponse = $this->getFileFromUrl($this->sourceModel->httpMethod, $this->sourceModel->sourceUrl, $event->getAuth());
                $content = $httpResponse->getBody()->getContents();

                break;

            case EntityImportSourceContainer::RETRIEVAL_TYPE_ABSOLUTE_PATH:
                $path = $this->sourceModel->absolutePath;

                if (file_exists($path)) {
                    $content = file_get_contents($path);
                }

                break;
        }

        $event = $this->eventDispatcher->dispatch(AfterFileSourceGetContentEvent::NAME, new AfterFileSourceGetContentEvent($content, $this->sourceModel));
        $content = $event->getContent();

        return $content;
    }

    protected function getFileFromUrl(string $method, string $url, array $auth = []): ResponseInterface
    {
        $client = new Client();

        return $client->request($method, \Contao\StringUtil::decodeEntities($url), $auth);
    }

    protected function getFileCache(string $cacheKey): string
    {
        $filesystemCache = $this->getFilesystemCache();

        return $filesystemCache->get('entity-import-file.'.$cacheKey, '');
    }

    protected function setFileCache(string $fileUrl, string $method, array $auth, string $cacheKey)
    {
        $filesystemCache = $this->getFilesystemCache();
        $response = $this->getFileFromUrl($method, $fileUrl, $auth);

        if (200 === $response->getStatusCode()) {
            $content = $response->getBody()->read(4096);
            $filesystemCache->set('entity-import-file.'.$cacheKey, $content);
        }
    }
}
