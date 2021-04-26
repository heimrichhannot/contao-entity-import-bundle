<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

use Ausi\SlugGenerator\SlugGenerator;
use HeimrichHannot\EntityImportBundle\DataContainer\EntityImportSourceContainer;
use HeimrichHannot\EntityImportBundle\Event\AfterFileSourceGetContentEvent;
use HeimrichHannot\EntityImportBundle\Event\BeforeAuthenticationEvent;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\String\StringUtil;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AbstractFileSource extends AbstractSource
{
    /**
     * @var FileUtil
     */
    protected $fileUtil;

    /**
     * @var StringUtil
     */
    protected $stringUtil;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ContainerUtil
     */
    private $containerUtil;

    /**
     * AbstractFileSource constructor.
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, FileUtil $fileUtil, StringUtil $stringUtil, ContainerUtil $containerUtil)
    {
        $this->fileUtil = $fileUtil;
        $this->stringUtil = $stringUtil;
        $this->containerUtil = $containerUtil;
        $this->eventDispatcher = $eventDispatcher;

        parent::__construct();
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
                $options = [];

                if (null !== $this->sourceModel->httpAuth) {
                    $httpAuth = \Contao\StringUtil::deserialize($this->sourceModel->httpAuth, true);
                    $auth = ['auth' => [$httpAuth['username'], $httpAuth['password']]];
                    $options = $auth;
                }

                // Check if SSL verification should be done
                if($this->sourceModel->dontCheckSSL) {
                    $options['verify'] = false;
                }

                $event = $this->eventDispatcher->dispatch(BeforeAuthenticationEvent::NAME, new BeforeAuthenticationEvent($auth, $this->sourceModel));

                if ($cache) {
                    $generator = new SlugGenerator();
                    $cacheKey = $generator->generate($this->sourceModel->sourceUrl);
                    $content = $this->getValueFromRemoteCache($cacheKey);

                    if (empty($content)) {
                        $this->storeValueToRemoteCache($this->sourceModel->sourceUrl, $cacheKey, $this->sourceModel->httpMethod, $options);
                        $content = $this->getValueFromRemoteCache($cacheKey);
                    }

                    break;
                }

                $result = $this->getContentFromUrl($this->sourceModel->httpMethod, $this->sourceModel->sourceUrl, $options);
                $content = $result['result'];

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
}
