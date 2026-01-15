<?php

namespace HeimrichHannot\EntityImportBundle\Source;

use Ausi\SlugGenerator\SlugGenerator;
use Contao\Message;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use HeimrichHannot\UtilsBundle\Util\Utils;

class YouTubeSource extends AbstractSource
{
    const MODE_CHANNEL = 'channel';
    const MODE_USER = 'user';
    const API_URL = 'https://www.googleapis.com/youtube/v3';
    const THUMBNAIL_URL = 'https://i1.ytimg.com/vi/%s/%s.jpg';
    const THUMBNAIL_SIZES = [
        'default',
        'mqdefault',
        'hqdefault',
        'sddefault',
        'maxresdefault',
    ];

    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
        protected Utils $utils,
        protected ParameterBagInterface $parameterBag,
        protected InsertTagParser $insertTagParser
    ) {
        parent::__construct($this->utils, $this->parameterBag, $this->insertTagParser);
    }

    public function getMappedData(array $options = []): array
    {
        $sourceModel = $this->sourceModel;
        $mapping = \Contao\StringUtil::deserialize($sourceModel->fieldMapping, true);

        $data = [];

        $videos = $this->getYouTubeResult();

        if (\is_array($videos) && !empty($videos)) {
            foreach ($videos as $video) {
                $data[] = $this->getMappedItemData($video, $mapping);
            }
        }

        return $data;
    }

    public function getPostFieldsAsOptions()
    {
        if (!$this->sourceModel->apiKey) {
            return [];
        }

        $videos = $this->getYouTubeResult();

        if (empty($videos)) {
            return [];
        }

        $options = [];

        foreach ($videos[0] as $field => $value) {
            if (\is_array($value)) {
                continue;
            }

            $options[$field] = $field;
        }

        $options['image_url'] = 'image_url';
        $options['url'] = 'url';

        unset($options['publishTime']);

        return $options;
    }

    protected function getYouTubeResult(): array
    {
        $data = [];
        $channelId = null;

        switch ($this->sourceModel->youtubeMode) {
            case static::MODE_CHANNEL:
                $channelId = $this->sourceModel->youtubeChannel;
                break;

            case static::MODE_USER:
                $userUrl = sprintf(
                    static::API_URL.'/channels?key=%s&forUsername=%s&part=id',
                    $this->sourceModel->apiKey,
                    $this->sourceModel->youtubeUsername
                );

                $slug = new SlugGenerator();
                $cacheKey = $slug->generate($userUrl);
                $content = $this->getValueFromRemoteCache($cacheKey);

                if (empty($content)) {
                    $result = $this->storeValueToRemoteCache($userUrl, $cacheKey, 'get');

                    if (200 !== $result['statusCode']) {
                        Message::addError(sprintf($GLOBALS['TL_LANG']['MSC']['newsroom']['serviceConnectionError'], $result['result'].' (Code '.$result['statusCode'].')'));
                        return [];
                    }

                    $content = $result['result'];
                }

                $responseData = json_decode($content, true);

                if (!\is_array($responseData['items']) || !isset($responseData['items'][0]['id'])) {
                    Message::addError($GLOBALS['TL_LANG']['MSC']['newsroom']['youTubeChannelIdCouldNotBeRetrieved']);
                    $this->deleteValueFromRemoteCache($cacheKey);
                    return [];
                }

                $channelId = $responseData['items'][0]['id'];
                break;
        }

        if (!$channelId) {
            return [];
        }

        $requestUrl = sprintf(
            static::API_URL.'/search?part=snippet&channelId=%s&maxResults=50&order=date&type=video&key=%s',
            $channelId,
            $this->sourceModel->apiKey
        );

        $slug = new SlugGenerator();
        $cacheKey = $slug->generate($requestUrl);
        $content = $this->getValueFromRemoteCache($cacheKey);

        if (empty($content)) {
            $result = $this->storeValueToRemoteCache($requestUrl, $cacheKey, 'get');

            if (200 !== $result['statusCode']) {
                Message::addError(sprintf($GLOBALS['TL_LANG']['MSC']['newsroom']['serviceConnectionError'], $result['result'].' (Code '.$result['statusCode'].')'));
                return [];
            }

            $content = $result['result'];
        }

        $responseData = json_decode($content, true);

        if (!\is_array($responseData) || empty($responseData)) {
            Message::addError($GLOBALS['TL_LANG']['MSC']['newsroom']['serviceNoPostsFound']);
            $this->deleteValueFromRemoteCache($cacheKey);
            return [];
        }

        $videos = $responseData['items'];

        if (\is_array($videos)) {
            foreach ($videos as $video) {
                $data[] = array_merge($video['snippet'], [
                    'id' => $video['id']['videoId'],
                ]);
            }
        }

        return $data;
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
                    case 'publishedAt':
                        $result[$mappingElement['name']] = $this->parseDate($value);
                        break;

                    case 'description':
                        $clean = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);
                        $result[$mappingElement['name']] = mb_encode_numericentity($clean, [0x80, 0x10FFFF, 0, 0xFFFF], 'UTF-8');
                        break;

                    case 'image_url':
                        $result[$mappingElement['name']] = $this->getHighestQualityThumbnailImage($element);
                        break;

                    case 'url':
                        $result[$mappingElement['name']] = 'https://www.youtube.com/watch?v='.$element['id'];
                        break;

                    default:
                        $result[$mappingElement['name']] = null === $value ? '' : $value;
                        break;
                }
            }
        }

        return $result;
    }

    protected function getHighestQualityThumbnailImage($element)
    {
        $maxSize = 0;
        $result = false;

        foreach (static::THUMBNAIL_SIZES as $size) {
            $url = sprintf(static::THUMBNAIL_URL, $element['id'], $size);

            $dimensions = @getimagesize($url);

            if (false === $dimensions || !isset($dimensions[0]) || !isset($dimensions[1])) {
                continue;
            }

            $width = $dimensions[0];
            $height = $dimensions[1];

            if (!$result || $width * $height > $maxSize) {
                $result = $url;
            }
        }

        return $result;
    }

    protected function parseDate($date)
    {
        $dateTime = \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $date);

        if (false !== $dateTime) {
            return $dateTime->getTimestamp();
        }

        return 0;
    }
}
