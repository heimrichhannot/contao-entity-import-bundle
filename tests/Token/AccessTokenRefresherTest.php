<?php

/*
 * Copyright (c) 2026 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Tests\Token;

use Doctrine\DBAL\Connection;
use GuzzleHttp\Psr7\Response;
use HeimrichHannot\EntityImportBundle\Exception\AccessTokenException;
use HeimrichHannot\EntityImportBundle\Tests\HttpClientTrait;
use HeimrichHannot\EntityImportBundle\Token\AccessTokenRefresher;
use PHPUnit\Framework\TestCase;

class AccessTokenRefresherTest extends TestCase
{
    use HttpClientTrait;

    private const INSTAGRAM_SOURCE = [
        'id' => 7,
        'type' => 'instagram',
        'instagramApi' => 'instagram',
        'instagramAccessToken' => 'IGAA-old-test-token',
        'accessTokenExpiration' => '',
    ];

    public function testSupportsOnlyInstagramLoginSourcesWithAToken(): void
    {
        $refresher = new AccessTokenRefresher($this->createStub(Connection::class));

        $this->assertTrue($refresher->supports(self::INSTAGRAM_SOURCE));
        $this->assertFalse($refresher->supports(['instagramApi' => 'facebook'] + self::INSTAGRAM_SOURCE));
        $this->assertFalse($refresher->supports(['instagramAccessToken' => ''] + self::INSTAGRAM_SOURCE));
        $this->assertFalse($refresher->supports(['type' => 'youtube'] + self::INSTAGRAM_SOURCE));
    }

    public function testRefreshesTheInstagramTokenAndStoresTokenAndExpiry(): void
    {
        $client = $this->mockHttpClient([
            new Response(200, [], json_encode(['access_token' => 'IGAA-new-test-token', 'token_type' => 'bearer', 'expires_in' => 5184000])),
        ]);

        $connection = $this->createMock(Connection::class);
        $connection->method('fetchAssociative')->willReturn(self::INSTAGRAM_SOURCE);
        $connection
            ->expects($this->once())
            ->method('update')
            ->with('tl_entity_import_source', $this->callback(function (array $set): bool {
                $this->assertSame('IGAA-new-test-token', $set['instagramAccessToken']);
                $this->assertEqualsWithDelta(time() + 5184000, (int) $set['accessTokenExpiration'], 5);

                return true;
            }), ['id' => 7])
        ;

        $expires = (new AccessTokenRefresher($connection, $client))->refresh(7);

        $this->assertEqualsWithDelta(time() + 5184000, $expires, 5);

        $request = $this->history[0]['request'];
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('graph.instagram.com', $request->getUri()->getHost());
        $this->assertSame('/refresh_access_token', $request->getUri()->getPath());
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertSame(['grant_type' => 'ig_refresh_token', 'access_token' => 'IGAA-old-test-token'], $query);
    }

    public function testReportsTheApiErrorWithoutTheToken(): void
    {
        $client = $this->mockHttpClient([
            new Response(400, [], json_encode(['error' => ['message' => 'Session has expired', 'type' => 'OAuthException', 'code' => 190]])),
        ]);

        $connection = $this->createMock(Connection::class);
        $connection->method('fetchAssociative')->willReturn(self::INSTAGRAM_SOURCE);
        $connection->expects($this->never())->method('update');

        try {
            (new AccessTokenRefresher($connection, $client))->refresh(7);
            $this->fail('No exception thrown');
        } catch (AccessTokenException $e) {
            $this->assertSame('HTTP 400: Session has expired', $e->getMessage());
            $this->assertStringNotContainsString('IGAA-old-test-token', $e->getMessage());
        }
    }

    public function testRejectsAnAnswerWithoutToken(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('fetchAssociative')->willReturn(self::INSTAGRAM_SOURCE);
        $connection->expects($this->never())->method('update');

        $this->expectException(AccessTokenException::class);

        (new AccessTokenRefresher($connection, $this->mockHttpClient([new Response(200, [], '{}')])))->refresh(7);
    }

    public function testRejectsASourceWithoutRefreshableToken(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('fetchAssociative')->willReturn(['instagramApi' => 'facebook'] + self::INSTAGRAM_SOURCE);
        $connection->expects($this->never())->method('update');

        $this->expectException(AccessTokenException::class);

        (new AccessTokenRefresher($connection, $this->mockHttpClient([])))->refresh(7);
    }

    public function testRefreshesTheLinkedInTokenWithTheRefreshToken(): void
    {
        $source = [
            'id' => 9,
            'type' => 'linkedin',
            'appId' => '78abc',
            'appSecret' => 'test-client-secret',
            'linkedinRefreshToken' => 'AQX-refresh',
            'linkedinRefreshTokenExpires' => (string) (time() + 200 * 86400),
        ];

        $refresher = new AccessTokenRefresher($this->createStub(Connection::class));
        $this->assertTrue($refresher->supports($source));
        $this->assertFalse($refresher->supports(['linkedinRefreshToken' => ''] + $source));
        $this->assertFalse($refresher->supports(['linkedinRefreshTokenExpires' => (string) (time() - 1)] + $source));

        $connection = $this->createMock(Connection::class);
        $connection->method('fetchAssociative')->willReturn($source);
        $connection
            ->expects($this->once())
            ->method('update')
            ->with('tl_entity_import_source', $this->callback(function (array $set): bool {
                $this->assertSame('AQV-new', $set['linkedinAccessToken']);
                $this->assertSame('AQX-refresh', $set['linkedinRefreshToken']);
                $this->assertEqualsWithDelta(time() + 199 * 86400, (int) $set['linkedinRefreshTokenExpires'], 5);

                return true;
            }), ['id' => 9])
        ;

        $client = $this->mockHttpClient([
            new Response(200, [], json_encode(['access_token' => 'AQV-new', 'expires_in' => 5184000, 'refresh_token' => 'AQX-refresh', 'refresh_token_expires_in' => 199 * 86400])),
        ]);

        $this->assertEqualsWithDelta(time() + 5184000, (new AccessTokenRefresher($connection, $client))->refresh(9), 5);

        parse_str((string) $this->history[0]['request']->getBody(), $body);
        $this->assertSame(['grant_type' => 'refresh_token', 'refresh_token' => 'AQX-refresh', 'client_id' => '78abc', 'client_secret' => 'test-client-secret'], $body);
    }
}
