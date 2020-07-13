<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

use Contao\Model;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Cache\Simple\FilesystemCache;

abstract class AbstractSource implements SourceInterface
{
    /**
     * @var array
     */
    protected $fieldMapping;

    /**
     * @var Model
     */
    protected $sourceModel;

    /**
     * @var FilesystemCache
     */
    protected $filesystemCache;

    public function __construct()
    {
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

    public function getFilesystemCache(): FilesystemCache
    {
        if (null === $this->filesystemCache) {
            $this->filesystemCache = new FilesystemCache('contaoEntityImportBundle', 300);
        }

        return $this->filesystemCache;
    }

    protected function getMappedItemData(?array $element, array $mapping): array
    {
        $result = [];

        foreach ($mapping as $mappingElement) {
            if ('static_value' === $mappingElement['valueType']) {
                $result[$mappingElement['name']] = $this->stringUtil->replaceInsertTags($mappingElement['staticValue']);
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
            $response = $client->request($method, \Contao\StringUtil::decodeEntities($url), $auth);
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

        return $filesystemCache->get('entity-import-remote.'.$cacheKey, '');
    }

    protected function deleteValueFromRemoteCache(string $cacheKey): string
    {
        $filesystemCache = $this->getFilesystemCache();

        return $filesystemCache->deleteItem('entity-import-remote.'.$cacheKey);
    }

    protected function storeValueToRemoteCache(string $url, string $cacheKey, string $method, array $auth = [])
    {
        $filesystemCache = $this->getFilesystemCache();

        $response = $this->getContentFromUrl($method, $url, $auth);

        if (200 === $response['statusCode']) {
            $filesystemCache->set('entity-import-remote.'.$cacheKey, $response['result']);
        }

        return [
            'statusCode' => $response['statusCode'],
            'result' => $response['result'],
        ];
    }

    protected function getValueFromDataCache(string $cacheKey): string
    {
        $filesystemCache = $this->getFilesystemCache();

        return $filesystemCache->get('entity-import-data.'.$cacheKey, '');
    }

    protected function deleteValueFromDataCache(string $cacheKey): string
    {
        $filesystemCache = $this->getFilesystemCache();

        return $filesystemCache->deleteItem('entity-import-data.'.$cacheKey);
    }

    protected function storeValueToDataCache(string $cacheKey, string $data)
    {
        $filesystemCache = $this->getFilesystemCache();

        $filesystemCache->set('entity-import-data.'.$cacheKey, $data);
    }
}
