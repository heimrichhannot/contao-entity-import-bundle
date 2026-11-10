<?php

/*
 * Copyright (c) 2026 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Source;

use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\Message;
use Contao\StringUtil;
use HeimrichHannot\EntityImportBundle\Exception\ApiException;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * The latest 50 public videos of a YouTube channel (YouTube Data API v3, API key only).
 *
 * The channel is found by its ID, its handle (@name) or its legacy user name; its videos are read from its uploads
 * playlist, which costs 1 quota unit per request (a search costs 100 of the 10,000 daily units).
 */
class YouTubeSource extends AbstractSource
{
    public const MODE_CHANNEL = 'channel';
    public const MODE_HANDLE = 'handle';
    public const MODE_USER = 'user';

    public const MODES = [self::MODE_CHANNEL, self::MODE_HANDLE, self::MODE_USER];

    public const API_URL = 'https://www.googleapis.com/youtube/v3';

    public const FIELDS = ['id', 'title', 'description', 'publishedAt', 'channelId', 'channelTitle', 'image_url', 'url'];

    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
        protected Utils $utils,
        protected ParameterBagInterface $parameterBag,
        protected InsertTagParser $insertTagParser,
    ) {
        parent::__construct($this->utils, $this->parameterBag, $this->insertTagParser);
    }

    public function getMappedData(array $options = []): array
    {
        $mapping = StringUtil::deserialize($this->sourceModel->fieldMapping, true);

        return array_map(fn (array $video): array => $this->getMappedItemData($video, $mapping), $this->getVideos());
    }

    public function getPostFieldsAsOptions(): array
    {
        return static::getFieldOptions();
    }

    /**
     * The video fields for the field mapping.
     */
    public static function getFieldOptions(): array
    {
        return array_combine(static::FIELDS, static::FIELDS);
    }

    /**
     * @return list<array<string, string>> The videos with the fields in FIELDS
     */
    public function getVideos(): array
    {
        if (!$this->sourceModel->apiKey) {
            return [];
        }

        try {
            if (null === ($playlistId = $this->getUploadsPlaylistId())) {
                Message::addError($GLOBALS['TL_LANG']['MSC']['entityImport']['youTubeChannelIdCouldNotBeRetrieved'] ?? 'The YouTube channel could not be found.');

                return [];
            }

            $result = $this->getJson(static::API_URL.'/playlistItems', [
                'part' => 'snippet,contentDetails,status',
                'playlistId' => $playlistId,
                'maxResults' => 50,
                'key' => $this->sourceModel->apiKey,
            ]);
        } catch (ApiException $e) {
            Message::addError(sprintf($GLOBALS['TL_LANG']['MSC']['entityImport']['serviceConnectionError'] ?? '%s', StringUtil::specialchars($e->getMessage())));

            return [];
        }

        $videos = [];

        foreach ($result['items'] ?? [] as $item) {
            // private and deleted videos stay in the playlist
            if ('public' !== ($item['status']['privacyStatus'] ?? 'public') || empty($item['snippet']['resourceId']['videoId'])) {
                continue;
            }

            $snippet = $item['snippet'];
            $id = $snippet['resourceId']['videoId'];

            $videos[] = [
                'id' => $id,
                'title' => $snippet['title'] ?? '',
                'description' => $snippet['description'] ?? '',
                'publishedAt' => $item['contentDetails']['videoPublishedAt'] ?? $snippet['publishedAt'] ?? '',
                'channelId' => $snippet['videoOwnerChannelId'] ?? $snippet['channelId'] ?? '',
                'channelTitle' => $snippet['videoOwnerChannelTitle'] ?? $snippet['channelTitle'] ?? '',
                'image_url' => static::getLargestThumbnail($snippet['thumbnails'] ?? []),
                'url' => 'https://www.youtube.com/watch?v='.$id,
            ];
        }

        return $videos;
    }

    /**
     * @throws ApiException
     */
    protected function getUploadsPlaylistId(): ?string
    {
        $query = match ($this->sourceModel->youtubeMode) {
            static::MODE_HANDLE => ['forHandle' => '@'.ltrim(trim((string) $this->sourceModel->youtubeHandle), '@')],
            static::MODE_USER => ['forUsername' => trim((string) $this->sourceModel->youtubeUsername)],
            default => ['id' => trim((string) $this->sourceModel->youtubeChannel)],
        };

        if ('' === trim(current($query), '@')) {
            return null;
        }

        $result = $this->getJson(static::API_URL.'/channels', $query + [
            'part' => 'contentDetails',
            'key' => $this->sourceModel->apiKey,
        ]);

        return $result['items'][0]['contentDetails']['relatedPlaylists']['uploads'] ?? null;
    }

    protected function getMappedItemData(?array $element, array $mapping): array
    {
        $result = [];

        foreach ($mapping as $mappingElement) {
            if ('static_value' === $mappingElement['valueType']) {
                $result[$mappingElement['name']] = $this->insertTagParser->replace((string) $mappingElement['staticValue']);

                continue;
            }

            if ('source_value' !== $mappingElement['valueType']) {
                continue;
            }

            $value = $element[$mappingElement['sourceValue']] ?? '';

            $result[$mappingElement['name']] = match ($mappingElement['sourceValue']) {
                'publishedAt' => static::parseDate($value),
                // control characters (but line breaks) out, everything beyond ASCII as numeric entities, so 4-byte
                // characters such as emoji fit into utf8mb3 columns
                'description' => mb_encode_numericentity((string) preg_replace('/[\x00-\x09\x0B-\x1F\x7F]/u', '', (string) $value), [0x80, 0x10FFFF, 0, 0x1FFFFF], 'UTF-8'),
                default => $value,
            };
        }

        return $result;
    }

    public static function getLargestThumbnail(array $thumbnails): string
    {
        $url = '';
        $maxSize = -1;

        foreach ($thumbnails as $thumbnail) {
            $size = (int) ($thumbnail['width'] ?? 0) * (int) ($thumbnail['height'] ?? 0);

            if (!empty($thumbnail['url']) && $size > $maxSize) {
                $url = $thumbnail['url'];
                $maxSize = $size;
            }
        }

        return $url;
    }

    public static function parseDate(mixed $date): int
    {
        try {
            return '' !== (string) $date ? (new \DateTimeImmutable((string) $date))->getTimestamp() : 0;
        } catch (\Exception) {
            return 0;
        }
    }
}
