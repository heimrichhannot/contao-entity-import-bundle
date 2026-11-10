<?php

/*
 * Copyright (c) 2026 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Tests\Meta;

use Doctrine\DBAL\Connection;
use GuzzleHttp\Psr7\Response;
use HeimrichHannot\EntityImportBundle\Exception\AccessTokenException;
use HeimrichHannot\EntityImportBundle\Meta\MetaLogin;
use HeimrichHannot\EntityImportBundle\Tests\ContaoMessagesTrait;
use HeimrichHannot\EntityImportBundle\Tests\HttpClientTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

class MetaLoginTest extends TestCase
{
    use ContaoMessagesTrait;
    use HttpClientTrait;

    private const SOURCE = [
        'id' => 7,
        'type' => 'facebook',
        'appId' => '555',
        'appSecret' => 'test-app-secret',
        'metaApiVersion' => 'v24.0',
        'facebookPageId' => '',
        'instagramUserID' => '',
    ];

    private const PAGES = ['data' => [
        ['id' => '100', 'name' => 'BEG', 'access_token' => 'EAA-page-100', 'instagram_business_account' => ['id' => '17841400000000001', 'username' => 'beg']],
        ['id' => '200', 'name' => 'Other page', 'access_token' => 'EAA-page-200'],
    ]];

    protected function setUp(): void
    {
        $this->setUpBackendRequest([], [], 'https://example.test/contao?do=entityImport&act=edit&id=7');
    }

    public function testBuildsTheLoginUrlAndRemembersStateAndBackUrl(): void
    {
        $url = $this->createLogin($this->createStub(Connection::class), [])->getAuthorizationUrl(self::SOURCE);

        $this->assertStringStartsWith('https://www.facebook.com/v24.0/dialog/oauth?', $url);
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        $this->assertSame('555', $query['client_id']);
        $this->assertSame('https://example.test/contao-newsroom/facebook/7/redirect_callback', $query['redirect_uri']);
        $this->assertSame('pages_show_list,pages_read_engagement', $query['scope']);

        $login = $this->session->get('huh_entity_import_meta_login.7');
        $this->assertSame($query['state'], $login['state']);
        $this->assertSame('https://example.test/contao?act=edit&do=entityImport&id=7', $login['backUrl']);
    }

    public function testHasNoLoginUrlWithoutAppCredentials(): void
    {
        $this->assertNull($this->createLogin($this->createStub(Connection::class), [])->getAuthorizationUrl(['appSecret' => ''] + self::SOURCE));
    }

    public function testRejectsAnInvalidGraphApiVersion(): void
    {
        $this->expectException(AccessTokenException::class);

        $this->createLogin($this->createStub(Connection::class), [])->getAuthorizationUrl(['metaApiVersion' => '24'] + self::SOURCE);
    }

    public function testRejectsACallbackWithAnotherState(): void
    {
        $this->session->set('huh_entity_import_meta_login.7', ['state' => 'expected', 'backUrl' => '/contao']);

        $this->expectException(AccessTokenException::class);
        $this->expectExceptionMessage('invalid state');

        $this->createLogin($this->createStub(Connection::class), [])->handleCallback(7, 'code', 'forged');
    }

    public function testStoresThePageTokenOfTheOnlyMatchingPage(): void
    {
        $connection = $this->mockConnection(['facebookPageId' => '200'] + self::SOURCE, [
            'facebookPageId' => '200',
            'facebookAccessToken' => 'EAA-page-200',
            'accessTokenExpiration' => '',
        ]);

        $backUrl = $this->createLogin($connection, $this->tokenResponses())->handleCallback(7, 'auth-code', 'state-1');

        $this->assertSame('/contao?do=entityImport&act=edit&id=7', $backUrl);

        // code -> short-lived token -> long-lived token -> pages
        $this->assertCount(3, $this->history);
        parse_str((string) $this->history[0]['request']->getBody(), $body);
        $this->assertSame(['client_id' => '555', 'client_secret' => 'test-app-secret', 'redirect_uri' => 'https://example.test/contao-newsroom/facebook/7/redirect_callback', 'grant_type' => 'authorization_code', 'code' => 'auth-code'], $body);
        parse_str((string) $this->history[1]['request']->getBody(), $body);
        $this->assertSame(['fb_exchange_token', 'EAA-short'], [$body['grant_type'], $body['fb_exchange_token']]);
        $this->assertSame('/v24.0/me/accounts', $this->history[2]['request']->getUri()->getPath());
        $this->assertSame('EAA-long', $this->requestQuery(2)['access_token']);
    }

    public function testAsksForThePageIfTheUserHasSeveral(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('fetchAssociative')->willReturn(self::SOURCE);
        $connection->expects($this->never())->method('update');

        $this->expectException(AccessTokenException::class);
        $this->expectExceptionMessage('Please enter the ID of the page to import. Available: "BEG" (ID 100), "Other page" (ID 200)');

        $this->createLogin($connection, $this->tokenResponses())->handleCallback(7, 'auth-code', 'state-1');
    }

    public function testStoresTheTokenOfThePageConnectedToTheInstagramAccount(): void
    {
        $connection = $this->mockConnection(['type' => 'instagram'] + self::SOURCE, [
            'instagramUserID' => '17841400000000001',
            'instagramAccessToken' => 'EAA-page-100',
            'accessTokenExpiration' => '',
        ]);

        $this->createLogin($connection, $this->tokenResponses())->handleCallback(7, 'auth-code', 'state-1');
    }

    public function testReportsADeniedLogin(): void
    {
        $this->session->set('huh_entity_import_meta_login.7', ['state' => 'state-1', 'backUrl' => '/contao']);

        $this->expectException(AccessTokenException::class);
        $this->expectExceptionMessage('Facebook did not grant access: Permissions error.');

        $this->createLogin($this->createStub(Connection::class), [])->handleCallback(7, null, 'state-1', 'Permissions error');
    }

    private function tokenResponses(): array
    {
        $this->session->set('huh_entity_import_meta_login.7', ['state' => 'state-1', 'backUrl' => '/contao?do=entityImport&act=edit&id=7']);

        $json = ['Content-Type' => 'application/json'];

        return [
            new Response(200, $json, json_encode(['access_token' => 'EAA-short', 'token_type' => 'bearer', 'expires_in' => 3600])),
            new Response(200, $json, json_encode(['access_token' => 'EAA-long', 'token_type' => 'bearer', 'expires_in' => 5183944])),
            new Response(200, $json, json_encode(self::PAGES)),
        ];
    }

    private function mockConnection(array $source, array $expectedSet): Connection
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('fetchAssociative')->willReturn($source);
        $connection
            ->expects($this->once())
            ->method('update')
            ->with('tl_entity_import_source', $this->callback(static fn (array $set): bool => array_intersect_key($set, $expectedSet) === $expectedSet), ['id' => 7])
        ;

        return $connection;
    }

    private function createLogin(Connection $connection, array $responses): MetaLogin
    {
        $router = $this->createStub(RouterInterface::class);
        $router->method('generate')->willReturnCallback(static fn (string $route, array $parameters): string => sprintf('http://example.test/contao-newsroom/facebook/%s/redirect_callback', $parameters['importSource']));

        return new MetaLogin($connection, $this->requestStack, $router, $this->mockHttpClient($responses));
    }
}
