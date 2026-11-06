<?php

namespace HeimrichHannot\EntityImportBundle\Source;

use Contao\StringUtil;
use Ausi\SlugGenerator\SlugGenerator;
use Contao\Message;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class InstagramSource extends AbstractSource
{
    const MEDIA_URL = 'https://graph.facebook.com/%s/%s/media?access_token=%s&fields=caption,id,is_shared_to_feed,media_type,media_url,permalink,thumbnail_url,timestamp,username&limit=100';

    /**
     * Instagram API with Instagram Login: the token belongs to the account, so no app or user ID is needed.
     */
    const INSTAGRAM_LOGIN_MEDIA_URL = 'https://graph.instagram.com/%s/me/media?access_token=%s&fields=caption,id,is_shared_to_feed,media_type,media_url,permalink,thumbnail_url,timestamp,username&limit=100';

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
        if (!$this->hasCredentials()) {
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
        $mapping = StringUtil::deserialize($sourceModel->fieldMapping, true);

        $data = [];

        $posts = $this->getInstagramResult();

        if (\is_array($posts) && !empty($posts)) {
            foreach ($posts as $post) {
                // Reels that are not shown in the profile's feed
                if (false === ($post['is_shared_to_feed'] ?? null)) {
                    continue;
                }

                $data[] = $this->getMappedItemData($post, $mapping);
            }
        }

        return $data;
    }

    protected function getInstagramResult(): array
    {
        $slug = new SlugGenerator();
        $cacheKey = $slug->generate(md5($this->sourceModel->appId . '-' . $this->sourceModel->instagramAccessToken));
        $mediaUrl = $this->getMediaUrl();

        $result = $this->getValueFromRemoteCache($cacheKey);

        if (empty($result)) {
            $client = new Client();

            try {
                $response = $client->request('get', $mediaUrl);
            } catch (RequestException $e) {
                Message::addError($e->getMessage());
                return [];
            }

            $responseData = json_decode($response->getBody()->getContents(), true);

            if (isset($responseData['data']) && \is_array($responseData['data'])) {
                $result = $responseData['data'];
                $this->storeValueToRemoteCache($mediaUrl, $cacheKey, 'get');
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
                        $result[$mappingElement['name']] = $value ?? '';
                        break;
                }
            }
        }

        return $result;
    }

    protected function isInstagramLogin(): bool
    {
        return 'instagram' === $this->sourceModel->instagramApi;
    }

    protected function hasCredentials(): bool
    {
        if ($this->isInstagramLogin()) {
            return (bool) $this->sourceModel->instagramAccessToken;
        }

        return $this->sourceModel->appId && $this->sourceModel->instagramAccessToken && $this->sourceModel->instagramUserID;
    }

    protected function getMediaUrl(): string
    {
        if ($this->isInstagramLogin()) {
            return sprintf(static::INSTAGRAM_LOGIN_MEDIA_URL, $this->sourceModel->metaApiVersion, $this->sourceModel->instagramAccessToken);
        }

        return sprintf(
            static::MEDIA_URL,
            $this->sourceModel->metaApiVersion,
            $this->sourceModel->instagramUserID,
            $this->sourceModel->instagramAccessToken
        );
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
