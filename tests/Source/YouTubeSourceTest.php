<?php

/*
 * Copyright (c) 2026 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Tests\Source;

use GuzzleHttp\Psr7\Response;
use HeimrichHannot\EntityImportBundle\Source\YouTubeSource;

class YouTubeSourceTest extends SourceTestCase
{
    private const MODEL = [
        'type' => 'youtube',
        'apiKey' => 'test-api-key',
        'youtubeMode' => 'handle',
        'youtubeHandle' => 'bahnland',
        'youtubeChannel' => '',
        'youtubeUsername' => '',
    ];

    public function testFindsTheChannelByHandleAndMapsItsPublicUploads(): void
    {
        $source = $this->createSource(YouTubeSource::class, self::MODEL + [
            'fieldMapping' => self::mapping(YouTubeSource::FIELDS, ['pid' => '5']),
        ], [
            new Response(200, [], json_encode(['items' => [
                ['id' => 'UCabc', 'contentDetails' => ['relatedPlaylists' => ['uploads' => 'UUabc']]],
            ]])),
            new Response(200, [], json_encode(['items' => [
                [
                    'snippet' => [
                        'publishedAt' => '2026-11-06T09:00:00Z',
                        'channelId' => 'UCabc',
                        'title' => 'Mit der Bahn ans Meer',
                        'description' => "Zeile 1\nZeile 2 \u{1F686}\u{0007}",
                        'thumbnails' => [
                            'default' => ['url' => 'https://i.ytimg.com/vi/v1/default.jpg', 'width' => 120, 'height' => 90],
                            'maxres' => ['url' => 'https://i.ytimg.com/vi/v1/maxresdefault.jpg', 'width' => 1280, 'height' => 720],
                            'high' => ['url' => 'https://i.ytimg.com/vi/v1/hqdefault.jpg', 'width' => 480, 'height' => 360],
                        ],
                        'channelTitle' => 'Bahnland',
                        'videoOwnerChannelTitle' => 'Bahnland',
                        'videoOwnerChannelId' => 'UCabc',
                        'resourceId' => ['kind' => 'youtube#video', 'videoId' => 'v1'],
                    ],
                    'contentDetails' => ['videoId' => 'v1', 'videoPublishedAt' => '2026-11-05T16:43:33Z'],
                    'status' => ['privacyStatus' => 'public'],
                ],
                [
                    'snippet' => ['title' => 'Private video', 'resourceId' => ['videoId' => 'v2'], 'thumbnails' => []],
                    'status' => ['privacyStatus' => 'private'],
                ],
            ]])),
        ]);

        $this->assertSame([[
            'id' => 'v1',
            'title' => 'Mit der Bahn ans Meer',
            'description' => "Zeile 1\nZeile 2 &#128646;",
            'publishedAt' => gmmktime(16, 43, 33, 11, 5, 2026),
            'channelId' => 'UCabc',
            'channelTitle' => 'Bahnland',
            'image_url' => 'https://i.ytimg.com/vi/v1/maxresdefault.jpg',
            'url' => 'https://www.youtube.com/watch?v=v1',
            'pid' => '5',
        ]], $source->getMappedData());

        $this->assertSame('/youtube/v3/channels', $this->history[0]['request']->getUri()->getPath());
        $this->assertSame(['forHandle' => '@bahnland', 'part' => 'contentDetails', 'key' => 'test-api-key'], $this->requestQuery(0));
        $this->assertSame('/youtube/v3/playlistItems', $this->history[1]['request']->getUri()->getPath());
        $this->assertSame('UUabc', $this->requestQuery(1)['playlistId']);
    }

    public function testFindsTheChannelByIdOrLegacyUserName(): void
    {
        $channel = new Response(200, [], json_encode(['items' => []]));

        $this->createSource(YouTubeSource::class, ['youtubeMode' => 'channel', 'youtubeChannel' => 'UCabc'] + self::MODEL + ['fieldMapping' => ''], [$channel])->getVideos();
        $this->assertSame('UCabc', $this->requestQuery()['id']);

        $this->createSource(YouTubeSource::class, ['youtubeMode' => 'user', 'youtubeUsername' => 'bahnland'] + self::MODEL + ['fieldMapping' => ''], [clone $channel])->getVideos();
        $this->assertSame('bahnland', $this->requestQuery()['forUsername']);
    }

    public function testReportsAnUnknownChannel(): void
    {
        $GLOBALS['TL_LANG']['MSC']['entityImport']['youTubeChannelIdCouldNotBeRetrieved'] = 'Channel not found.';

        $source = $this->createSource(YouTubeSource::class, self::MODEL + ['fieldMapping' => ''], [
            new Response(200, [], json_encode(['kind' => 'youtube#channelListResponse', 'pageInfo' => ['totalResults' => 0]])),
        ]);

        $this->assertSame([], $source->getVideos());
        $this->assertSame(['Channel not found.'], $this->getMessages('error'));
        $this->assertCount(1, $this->history);
    }

    public function testReportsTheApiErrorWithoutTheKey(): void
    {
        $source = $this->createSource(YouTubeSource::class, self::MODEL + ['fieldMapping' => ''], [
            new Response(400, [], json_encode(['error' => ['code' => 400, 'message' => 'API key not valid. Please pass a valid API key.']])),
        ]);

        $this->assertSame([], $source->getVideos());
        $this->assertSame(['No posts: HTTP 400: API key not valid. Please pass a valid API key.'], $this->getMessages('error'));
    }
}
