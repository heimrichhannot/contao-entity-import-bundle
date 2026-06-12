<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

use Contao\StringUtil;
use Contao\Environment;
use Contao\Model;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Cache\CacheInterface;

abstract class AbstractSource implements SourceInterface
{
    protected array $fieldMapping;
    protected Model $sourceModel;
    protected CacheInterface $filesystemCache;
    protected string $domain;

    public function __construct(protected Utils $utils, protected ParameterBagInterface $parameterBag, protected InsertTagParser $insertTagParser)
    {
        $this->domain = '';
    }

    public function getMapping(): array
    {
        return $this->fieldMapping;
    }

    public function setFieldMapping(array $mapping): void
    {
        $this->fieldMapping = $mapping;
    }

    public function getSourceModel(): Model
    {
        return $this->sourceModel;
    }

    public function setSourceModel(Model $sourceModel): void
    {
        $this->sourceModel = $sourceModel;
    }

    public function getFilesystemCache(): CacheInterface
    {
        if (!isset($this->filesystemCache)) {
            $this->filesystemCache = new FilesystemAdapter(
                'contaoEntityImportBundle',
                300,
                $this->parameterBag->get('kernel.project_dir').'/var/cache/'.$this->parameterBag->get('kernel.environment')
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
                $result[$mappingElement['name']] = $this->replaceInsertTags($mappingElement['staticValue']);
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

        return $filesystemCache->get('entity-import-remote.'.$cacheKey, fn() => '');
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
            $filesystemCache->get('entity-import-remote.'.$cacheKey, fn() => trim((string) $response['result']));
        }

        return [
            'statusCode' => $response['statusCode'],
            'result' => $response['result'],
        ];
    }

    protected function getValueFromDataCache(string $cacheKey): string
    {
        $filesystemCache = $this->getFilesystemCache();

        return $filesystemCache->get('entity-import-data.'.$cacheKey, fn() => '');
    }

    protected function deleteValueFromDataCache(string $cacheKey): string
    {
        $filesystemCache = $this->getFilesystemCache();

        return $filesystemCache->delete('entity-import-data.'.$cacheKey);
    }

    protected function storeValueToDataCache(string $cacheKey, string $data)
    {
        $filesystemCache = $this->getFilesystemCache();

        $filesystemCache->get('entity-import-data.'.$cacheKey, fn() => $data);
    }

    protected function replaceInsertTags(?string $value): string
    {
        return $this->insertTagParser->replace((string) $value);
    }
}
