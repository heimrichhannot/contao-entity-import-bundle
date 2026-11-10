<?php

/*
 * Copyright (c) 2026 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Tests\LinkedIn;

use Doctrine\DBAL\Connection;
use GuzzleHttp\Psr7\Response;
use HeimrichHannot\EntityImportBundle\Exception\AccessTokenException;
use HeimrichHannot\EntityImportBundle\LinkedIn\LinkedInLogin;
use HeimrichHannot\EntityImportBundle\Tests\ContaoMessagesTrait;
use HeimrichHannot\EntityImportBundle\Tests\HttpClientTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

class LinkedInLoginTest extends TestCase
{
    use ContaoMessagesTrait;
    use HttpClientTrait;

    private const SOURCE = [
        'id' => 9,
        'type' => 'linkedin',
        'appId' => '78abc',
        'appSecret' => 'test-client-secret',
    ];

    protected function setUp(): void
    {
        $this->setUpBackendRequest([], [], 'https://example.test/contao?do=entityImport&act=edit&id=9');
    }

    public function testBuildsTheLoginUrlAndRemembersSourceStateAndBackUrl(): void
    {
        $url = $this->createLogin($this->createStub(Connection::class), [])->getAuthorizationUrl(self::SOURCE);

        $this->assertStringStartsWith('https://www.linkedin.com/oauth/v2/authorization?', $url);
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        $this->assertSame('code', $query['response_type']);
        $this->assertSame('78abc', $query['client_id']);
        $this->assertSame('https://example.test/contao-newsroom/auth/linkedin', $query['redirect_uri']);
        $this->assertSame('r_organization_social', $query['scope']);

        $login = $this->session->get('huh_entity_import_linkedin_login');
        $this->assertSame([$query['state'], 9], [$login['state'], $login['sourceId']]);
    }

    public function testStoresTheAccessAndRefreshToken(): void
    {
        $this->session->set('huh_entity_import_linkedin_login', ['state' => 's1', 'sourceId' => 9, 'backUrl' => '/contao?act=edit&id=9']);

        $connection = $this->createMock(Connection::class);
        $connection->method('fetchAssociative')->willReturn(self::SOURCE);
        $connection
            ->expects($this->once())
            ->method('update')
            ->with('tl_entity_import_source', $this->callback(function (array $set): bool {
                $this->assertSame('AQV-access', $set['linkedinAccessToken']);
                $this->assertSame('AQX-refresh', $set['linkedinRefreshToken']);
                $this->assertEqualsWithDelta(time() + 5184000, (int) $set['accessTokenExpiration'], 5);
                $this->assertEqualsWithDelta(time() + 31536000, (int) $set['linkedinRefreshTokenExpires'], 5);

                return true;
            }), ['id' => 9])
        ;

        $result = $this->createLogin($connection, [
            new Response(200, [], json_encode(['access_token' => 'AQV-access', 'expires_in' => 5184000, 'refresh_token' => 'AQX-refresh', 'refresh_token_expires_in' => 31536000, 'scope' => 'r_organization_social'])),
        ])->handleCallback('auth-code', 's1');

        $this->assertSame(['sourceId' => 9, 'backUrl' => '/contao?act=edit&id=9'], $result);

        $request = $this->history[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('https://www.linkedin.com/oauth/v2/accessToken', (string) $request->getUri());
        parse_str((string) $request->getBody(), $body);
        $this->assertSame([
            'grant_type' => 'authorization_code',
            'code' => 'auth-code',
            'redirect_uri' => 'https://example.test/contao-newsroom/auth/linkedin',
            'client_id' => '78abc',
            'client_secret' => 'test-client-secret',
        ], $body);
    }

    public function testRejectsAForeignState(): void
    {
        $this->session->set('huh_entity_import_linkedin_login', ['state' => 's1', 'sourceId' => 9]);

        $this->expectException(AccessTokenException::class);

        $this->createLogin($this->createStub(Connection::class), [])->handleCallback('auth-code', 'other');
    }

    public function testReportsTheOAuthError(): void
    {
        $this->session->set('huh_entity_import_linkedin_login', ['state' => 's1', 'sourceId' => 9]);

        $connection = $this->createStub(Connection::class);
        $connection->method('fetchAssociative')->willReturn(self::SOURCE);

        $this->expectException(AccessTokenException::class);
        $this->expectExceptionMessage('HTTP 400: Unable to retrieve access token: appid/redirect uri/code verifier does not match authorization code.');

        $this->createLogin($connection, [
            new Response(400, [], json_encode(['error' => 'invalid_request', 'error_description' => 'Unable to retrieve access token: appid/redirect uri/code verifier does not match authorization code.'])),
        ])->handleCallback('auth-code', 's1');
    }

    private function createLogin(Connection $connection, array $responses): LinkedInLogin
    {
        $router = $this->createStub(RouterInterface::class);
        $router->method('generate')->willReturn('https://example.test/contao-newsroom/auth/linkedin');

        return new LinkedInLogin($connection, $this->requestStack, $router, $this->mockHttpClient($responses));
    }
}
