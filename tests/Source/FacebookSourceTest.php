<?php

/*
 * Copyright (c) 2026 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Tests\Source;

use GuzzleHttp\Psr7\Response;
use HeimrichHannot\EntityImportBundle\Source\FacebookSource;

class FacebookSourceTest extends SourceTestCase
{
    private const MODEL = [
        'type' => 'facebook',
        'appSecret' => 'test-app-secret',
        'metaApiVersion' => 'v24.0',
        'facebookPageId' => '1234567890',
        'facebookAccessToken' => 'EAA-test-page-token',
    ];

    public function testMapsThePagePosts(): void
    {
        $source = $this->createSource(FacebookSource::class, self::MODEL + [
            'fieldMapping' => self::mapping(['id', 'message', 'created_time', 'permalink_url', 'full_picture'], ['pid' => '3']),
        ], [
            new Response(200, [], json_encode(['data' => [
                [
                    'id' => '1234567890_111',
                    'message' => 'Neue Strecke: "Hamburg" <-> Kiel',
                    'full_picture' => 'https://scontent.example/p.jpg',
                    'permalink_url' => 'https://www.facebook.com/1234567890/posts/111',
                    'created_time' => '2026-11-05T16:43:33+0000',
                    'status_type' => 'added_photos',
                ],
                [
                    'id' => '1234567890_112',
                    'created_time' => '2026-11-04T08:00:00+0000',
                    'status_type' => 'mobile_status_update',
                ],
            ]])),
        ]);

        $this->assertSame([
            [
                'id' => '1234567890_111',
                'message' => 'Neue Strecke: &quot;Hamburg&quot; &lt;-&gt; Kiel',
                'created_time' => gmmktime(16, 43, 33, 11, 5, 2026),
                'permalink_url' => 'https://www.facebook.com/1234567890/posts/111',
                'full_picture' => 'https://scontent.example/p.jpg',
                'pid' => '3',
            ],
            [
                'id' => '1234567890_112',
                'message' => '',
                'created_time' => gmmktime(8, 0, 0, 11, 4, 2026),
                'permalink_url' => '',
                'full_picture' => '',
                'pid' => '3',
            ],
        ], $source->getMappedData());

        $uri = $this->history[0]['request']->getUri();
        $this->assertSame('https://graph.facebook.com/v24.0/1234567890/posts', (string) $uri->withQuery(''));
        $this->assertSame([
            'fields' => FacebookSource::POST_FIELDS,
            'limit' => '100',
            'access_token' => 'EAA-test-page-token',
            'appsecret_proof' => hash_hmac('sha256', 'EAA-test-page-token', 'test-app-secret'),
        ], $this->requestQuery());
    }

    public function testShowsTheApiErrorWithoutTheToken(): void
    {
        $source = $this->createSource(FacebookSource::class, self::MODEL + ['fieldMapping' => self::mapping(['id'])], [
            new Response(400, [], json_encode(['error' => ['message' => 'Error validating access token', 'type' => 'OAuthException', 'code' => 190]])),
        ]);

        $this->assertSame([], $source->getMappedData());
        $this->assertSame(['No posts: HTTP 400: Error validating access token'], $this->getMessages('error'));
    }

    public function testDoesNotAskWithoutPageOrToken(): void
    {
        $source = $this->createSource(FacebookSource::class, ['facebookAccessToken' => ''] + self::MODEL + ['fieldMapping' => self::mapping(['id'])], []);

        $this->assertSame([], $source->getMappedData());
        $this->assertSame([], $this->history);
    }
}
