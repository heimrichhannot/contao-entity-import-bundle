<?php

namespace HeimrichHannot\EntityImportBundle\Source;

use Ausi\SlugGenerator\SlugGenerator;
use Contao\Message;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use HeimrichHannot\UtilsBundle\Util\Utils;
use LitEmoji\LitEmoji;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class InstagramSource extends AbstractSource
{
    const MEDIA_URL = 'https://graph.facebook.com/%s/%s/media?access_token=%s&fields=caption,id,media_type,media_url,permalink,thumbnail_url,timestamp,username&limit=100';

    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
        protected Utils $utils,
        protected ParameterBagInterface $parameterBag,
        protected InsertTagParser $insertTagParser
    ) {
        parent::__construct($this->utils, $this->parameterBag, $this->insertTagParser);
    }

    public function getPostFieldsAsOptions()
    {
        if (!$this->sourceModel->appId || !$this->sourceModel->instagramAccessToken || !$this->sourceModel->instagramUserID) {
            return [];
        }

        $posts = $this->getInstagramResult();

        if (false === $posts || \count($posts) < 1) {
            return [];
        }

        $options = [];

        foreach ($posts[0] as $field => $value) {
            if (\is_array($value)) {
                continue;
            }

            $options[$field] = $field;
        }

        return $options;
    }

    public function getMappedData(array $options = []): array
    {
        $sourceModel = $this->sourceModel;
        $mapping = \Contao\StringUtil::deserialize($sourceModel->fieldMapping, true);

        $data = [];

        $posts = $this->getInstagramResult();

        if (\is_array($posts) && !empty($posts)) {
            foreach ($posts as $post) {
                $data[] = $this->getMappedItemData($post, $mapping);
            }
        }

        return $data;
    }

    protected function getInstagramResult(): array
    {
        $slug = new SlugGenerator();
        $cacheKey = $slug->generate(md5($this->sourceModel->appId . '-' . $this->sourceModel->instagramAccessToken));

        $result = $this->getValueFromRemoteCache($cacheKey);

        if (empty($result)) {
            $client = new Client();

            try {
                $response = $client->request('get', sprintf(
                    static::MEDIA_URL,
                    $this->sourceModel->metaApiVersion,
                    $this->sourceModel->instagramUserID,
                    $this->sourceModel->instagramAccessToken
                ));
            } catch (RequestException $e) {
                Message::addError($e->getMessage());
                return [];
            }

            $responseData = json_decode($response->getBody()->getContents(), true);

            if (isset($responseData['data']) && \is_array($responseData['data'])) {
                $result = $responseData['data'];
                $this->storeValueToRemoteCache(
                    sprintf(
                        static::MEDIA_URL,
                        $this->sourceModel->metaApiVersion,
                        $this->sourceModel->instagramUserID,
                        $this->sourceModel->instagramAccessToken
                    ),
                    $cacheKey,
                    'get'
                );
            } else {
                return [];
            }
        } else {
            $result = json_decode($result, true)['data'];
        }

        return $result;
    }

    protected function getMappedItemData(?array $element, array $mapping): array
    {
        $result = [];

        foreach ($mapping as $mappingElement) {
            if ('static_value' === $mappingElement['valueType']) {
                $result[$mappingElement['name']] = $this->insertTagParser->replace($mappingElement['staticValue']);
            } elseif ('source_value' === $mappingElement['valueType']) {
                $value = $element[$mappingElement['sourceValue']] ?? null;

                if (\is_array($value)) {
                    continue;
                }

                switch ($mappingElement['sourceValue']) {
                    case 'timestamp':
                        $result[$mappingElement['name']] = $this->parseDate($value);
                        break;

                    case 'caption':
                        $result[$mappingElement['name']] = htmlspecialchars(strval($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        break;

                    case 'media_url':
                    case 'thumbnail_url':
                        switch ($element['media_type']) {
                            case 'IMAGE':
                            case 'CAROUSEL_ALBUM':
                                $result[$mappingElement['name']] = $element['media_url'];
                                break;

                            case 'VIDEO':
                                $result[$mappingElement['name']] = $element['thumbnail_url'];
                                break;
                        }
                        break;

                    default:
                        $result[$mappingElement['name']] = null === $value ? '' : $value;
                        break;
                }
            }
        }

        return $result;
    }

    protected function parseDate($date)
    {
        $dateTime = \DateTime::createFromFormat('Y-m-d\TH:i:s\+0000', $date);

        if (false !== $dateTime) {
            return $dateTime->getTimestamp();
        }

        return 0;
    }
}
