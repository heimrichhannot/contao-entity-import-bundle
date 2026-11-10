<?php

/*
 * Copyright (c) 2026 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Tests\Source;

use GuzzleHttp\Psr7\Response;
use HeimrichHannot\EntityImportBundle\Source\LinkedInSource;

class LinkedInSourceTest extends SourceTestCase
{
    private const MODEL = [
        'type' => 'linkedin',
        'appPageId' => '2414183',
        'linkedInVersion' => '202610',
        'linkedinAccessToken' => 'AQV-test-token',
        'numberPosts' => '10',
    ];

    private const POSTS = ['elements' => [
        [
            'id' => 'urn:li:share:7300000000000000001',
            'author' => 'urn:li:organization:2414183',
            'commentary' => 'Neu bei @[Bahnland GmbH](urn:li:organization:123): Fahrplan 2027 \\(Entwurf\\) {hashtag|\\#|Bahn} & mehr',
            'publishedAt' => 1762361013000,
            'lifecycleState' => 'PUBLISHED',
            'distribution' => ['feedDistribution' => 'MAIN_FEED'],
            'content' => ['media' => ['id' => 'urn:li:image:C4D10AQ']],
        ],
        [
            'id' => 'urn:li:ugcPost:7300000000000000002',
            'commentary' => 'Lesetipp',
            'createdAt' => 1762243200000,
            'lifecycleState' => 'PUBLISHED',
            'content' => ['article' => ['source' => 'https://example.org/artikel', 'title' => 'Ein "Artikel"']],
        ],
        [
            'id' => 'urn:li:share:7300000000000000003',
            'commentary' => 'Anzeige',
            'lifecycleState' => 'PUBLISHED',
            'distribution' => ['feedDistribution' => 'NONE'],
        ],
        [
            'id' => 'urn:li:share:7300000000000000004',
            'commentary' => 'Entwurf',
            'lifecycleState' => 'DRAFT',
        ],
    ]];

    public function testMapsThePublishedFeedPostsAndResolvesImagesForTheMapping(): void
    {
        $source = $this->createSource(LinkedInSource::class, self::MODEL + [
            'fieldMapping' => self::mapping(LinkedInSource::FIELDS),
        ], [
            new Response(200, [], json_encode(self::POSTS)),
            new Response(200, [], json_encode(['downloadUrl' => 'https://media.licdn.com/dms/image/C4D10AQ/x.jpg', 'status' => 'AVAILABLE'])),
        ]);

        $this->assertSame([
            [
                'id' => 'urn:li:share:7300000000000000001',
                'url' => 'https://www.linkedin.com/feed/update/urn:li:share:7300000000000000001/',
                'commentary' => 'Neu bei Bahnland GmbH: Fahrplan 2027 (Entwurf) #Bahn &amp; mehr',
                'publishedAt' => 1762361013,
                'image_url' => 'https://media.licdn.com/dms/image/C4D10AQ/x.jpg',
                'article_url' => '',
                'article_title' => '',
            ],
            [
                'id' => 'urn:li:ugcPost:7300000000000000002',
                'url' => 'https://www.linkedin.com/feed/update/urn:li:ugcPost:7300000000000000002/',
                'commentary' => 'Lesetipp',
                'publishedAt' => 1762243200,
                'image_url' => '',
                'article_url' => 'https://example.org/artikel',
                'article_title' => 'Ein &quot;Artikel&quot;',
            ],
        ], $source->getMappedData());

        $request = $this->history[0]['request'];
        $this->assertSame('https://api.linkedin.com/rest/posts', (string) $request->getUri()->withQuery(''));
        $this->assertSame(['author' => 'urn:li:organization:2414183', 'q' => 'author', 'count' => '10'], $this->requestQuery());
        $this->assertSame('Bearer AQV-test-token', $request->getHeaderLine('Authorization'));
        $this->assertSame('202610', $request->getHeaderLine('LinkedIn-Version'));
        $this->assertSame('2.0.0', $request->getHeaderLine('X-Restli-Protocol-Version'));
        $this->assertSame('/rest/images/urn%3Ali%3Aimage%3AC4D10AQ', $this->history[1]['request']->getUri()->getPath());
    }

    public function testSkipsTheImageRequestsIfTheMappingHasNoImage(): void
    {
        $source = $this->createSource(LinkedInSource::class, self::MODEL + ['fieldMapping' => self::mapping(['id'])], [
            new Response(200, [], json_encode(self::POSTS)),
        ]);

        $this->assertCount(2, $source->getMappedData());
        $this->assertCount(1, $this->history);
    }

    public function testShowsTheApiError(): void
    {
        $source = $this->createSource(LinkedInSource::class, self::MODEL + ['fieldMapping' => self::mapping(['id'])], [
            new Response(401, [], json_encode(['status' => 401, 'serviceErrorCode' => 65600, 'code' => 'INVALID_ACCESS_TOKEN', 'message' => 'Invalid access token'])),
        ]);

        $this->assertSame([], $source->getMappedData());
        $this->assertSame(['No posts: HTTP 401: Invalid access token'], $this->getMessages('error'));
    }

    public function testConvertsLittleTextToPlainText(): void
    {
        $this->assertSame(
            'Hallo Kiel & #Nahverkehr: [1] *fett* \\ (a|b) {x} <y> @team',
            LinkedInSource::littleTextToPlainText('Hallo @[Kiel](urn:li:organization:1) & {hashtag|\\#|Nahverkehr}: \\[1\\] \\*fett\\* \\\\ \\(a\\|b\\) \\{x\\} \\<y\\> \\@team'),
        );
    }
}
