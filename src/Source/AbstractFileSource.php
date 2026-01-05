<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

use Ausi\SlugGenerator\SlugGenerator;
use HeimrichHannot\EntityImportBundle\EventListener\DataContainer\EntityImportSourceContainer;
use HeimrichHannot\EntityImportBundle\Event\AfterFileSourceGetContentEvent;
use HeimrichHannot\EntityImportBundle\Event\BeforeAuthenticationEvent;
use Contao\FilesModel;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

abstract class AbstractFileSource extends AbstractSource
{
    /**
     * AbstractFileSource constructor.
     */
    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
        Utils $utils,
        ParameterBagInterface $parameterBag,
        InsertTagParser $insertTagParser
    )
    {
        parent::__construct($utils, $parameterBag, $insertTagParser);
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
        $projectDir = $this->parameterBag->get('kernel.project_dir');

        switch ($this->sourceModel->retrievalType) {
            case EntityImportSourceContainer::RETRIEVAL_TYPE_CONTAO_FILE_SYSTEM:
                $fileModel = FilesModel::findByUuid($this->sourceModel->fileSRC);

                if (null === $fileModel) {
                    break;
                }

                $path = $projectDir.'/'.$fileModel->path;

                if (file_exists($path)) {
                    $content = file_get_contents($path);
                }

                break;

            case EntityImportSourceContainer::RETRIEVAL_TYPE_HTTP:
                $auth = [];

                if (null !== $this->sourceModel->httpAuth) {
                    $httpAuth = \Contao\StringUtil::deserialize($this->sourceModel->httpAuth, true);
                    $auth = ['auth' => [$httpAuth['username'], $httpAuth['password']]];
                }

                if (!$this->sourceModel->httpMethod || !$this->sourceModel->sourceUrl) {
                    return '';
                }

                $event = $this->eventDispatcher->dispatch(new BeforeAuthenticationEvent($auth, $this->sourceModel), BeforeAuthenticationEvent::NAME);

                if ($cache) {
                    $generator = new SlugGenerator();
                    $cacheKey = $generator->generate($this->sourceModel->sourceUrl);
                    $content = $this->getValueFromRemoteCache($cacheKey);

                    if (empty($content)) {
                        $this->storeValueToRemoteCache($this->sourceModel->sourceUrl, $cacheKey, $this->sourceModel->httpMethod, $event->getAuth());
                        $content = $this->getValueFromRemoteCache($cacheKey);
                    }

                    break;
                }

                $result = $this->getContentFromUrl($this->sourceModel->httpMethod, $this->sourceModel->sourceUrl, $event->getAuth());
                $content = $result['result'];

                break;

            case EntityImportSourceContainer::RETRIEVAL_TYPE_ABSOLUTE_PATH:
                $path = $this->sourceModel->absolutePath;

                if (file_exists($path)) {
                    $content = file_get_contents($path);
                }

                break;
        }

        $event = $this->eventDispatcher->dispatch(new AfterFileSourceGetContentEvent($content, $this->sourceModel), AfterFileSourceGetContentEvent::NAME);
        $content = $event->getContent();

        return $content;
    }
}
