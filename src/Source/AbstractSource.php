<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\Environment;
use Contao\Model;
use Contao\StringUtil;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\Cache\CacheInterface;

abstract class AbstractSource implements SourceInterface
{
    protected ContainerInterface $container;
    protected array $fieldMapping;
    protected Model $sourceModel;
    protected CacheInterface $filesystemCache;
    protected string $domain;

    public function __construct()
    {
        $this->domain = '';
    }

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    public function getMapping(): array
    {
        return $this->fieldMapping;
    }

    public function setFieldMapping(array $mapping)
    {
        $this->fieldMapping = $mapping;
    }

    public function getSourceModel(): Model
    {
        return $this->sourceModel;
    }

    public function setSourceModel(Model $sourceModel)
    {
        $this->sourceModel = $sourceModel;
    }

    public function getFilesystemCache(): CacheInterface
    {
        if (!isset($this->filesystemCache)) {
            $this->filesystemCache = new FilesystemAdapter(
                'contaoEntityImportBundle',
                300,
                $this->container->getParameter('kernel.project_dir').'/var/cache/'.$this->container->getParameter('kernel.environment')
            );
        }

        return $this->filesystemCache;
    }

    /**
     * @return string
     */
    public function getDomain(): ?string
    {
        return $this->domain ?: Environment::get('url');
    }

    /**
     * @param string $domain
     */
    public function setDomain(?string $domain): void
    {
        $this->domain = $domain;
    }

    protected function getMappedItemData(?array $element, array $mapping): array
    {
        $result = [];

        foreach ($mapping as $mappingElement) {
            if ('static_value' === $mappingElement['valueType']) {
                $result[$mappingElement['name']] = $this->container->get(InsertTagParser::class)->replaceInsertTags($mappingElement['staticValue']);
            } elseif ('source_value' === $mappingElement['valueType']) {
                $result[$mappingElement['name']] = $element[$mappingElement['sourceValue']];
            }
        }

        return $result;
    }

    protected function getContentFromUrl(string $method, string $url, array $auth = []): array
    {
        $client = new Client();

        try {
            $response = $client->request($method, StringUtil::decodeEntities($url), $auth);
        } catch (RequestException $e) {
            return [
                'statusCode' => $e->getResponse()->getStatusCode(),
                'result' => $e->getResponse()->getBody()->getContents(),
            ];
        }

        return [
            'statusCode' => $response->getStatusCode(),
            'result' => $response->getBody()->getContents(),
        ];
    }

    protected function getValueFromRemoteCache(string $cacheKey): string
    {
        $filesystemCache = $this->getFilesystemCache();

        return $filesystemCache->get('entity-import-remote.'.$cacheKey, function () {
            return '';
        });
    }

    protected function deleteValueFromRemoteCache(string $cacheKey): string
    {
        $filesystemCache = $this->getFilesystemCache();

        return $filesystemCache->delete('entity-import-remote.'.$cacheKey);
    }

    protected function storeValueToRemoteCache(string $url, string $cacheKey, string $method, array $auth = [])
    {
        $filesystemCache = $this->getFilesystemCache();

        $response = $this->getContentFromUrl($method, $url, $auth);

        $filesystemCache->delete('entity-import-remote.'.$cacheKey);

        if (200 === $response['statusCode']) {
            $filesystemCache->get('entity-import-remote.'.$cacheKey, function () use ($response) {
                return trim($response['result']);
            });
        }

        return [
            'statusCode' => $response['statusCode'],
            'result' => $response['result'],
        ];
    }

    protected function getValueFromDataCache(string $cacheKey): string
    {
        $filesystemCache = $this->getFilesystemCache();

        return $filesystemCache->get('entity-import-data.'.$cacheKey, function () {
            return '';
        });
    }

    protected function deleteValueFromDataCache(string $cacheKey): string
    {
        $filesystemCache = $this->getFilesystemCache();

        return $filesystemCache->delete('entity-import-data.'.$cacheKey);
    }

    protected function storeValueToDataCache(string $cacheKey, string $data)
    {
        $filesystemCache = $this->getFilesystemCache();

        $filesystemCache->get('entity-import-data.'.$cacheKey, function () use ($data) {
            return $data;
        });
    }
}
