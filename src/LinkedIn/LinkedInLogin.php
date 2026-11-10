<?php

/*
 * Copyright (c) 2026 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\LinkedIn;

use Doctrine\DBAL\Connection;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use HeimrichHannot\EntityImportBundle\EventListener\DataContainer\EntityImportSourceContainer;
use HeimrichHannot\EntityImportBundle\Exception\AccessTokenException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * "Sign In with LinkedIn" (3-legged OAuth) for LinkedIn company page sources. Reading an organization's posts needs
 * the scope r_organization_social of LinkedIn's Community Management API, which LinkedIn grants after reviewing the
 * app. Access tokens last 60 days; apps with programmatic refresh tokens get a refresh token that lasts a year and
 * keeps the access token fresh (AccessTokenRefresher) until a new login is due.
 */
class LinkedInLogin
{
    public const ROUTE = 'newsroom_auth_linkedin';

    public const AUTHORIZATION_URL = 'https://www.linkedin.com/oauth/v2/authorization';

    public const TOKEN_URL = 'https://www.linkedin.com/oauth/v2/accessToken';

    public const SCOPE = 'r_organization_social';

    private const SESSION_KEY = 'huh_entity_import_linkedin_login';

    public function __construct(
        private readonly Connection $connection,
        private readonly RequestStack $requestStack,
        private readonly RouterInterface $router,
        private readonly ?ClientInterface $client = null,
    ) {
    }

    /**
     * The LinkedIn login URL for the source (a tl_entity_import_source row), or null without client ID and secret.
     * The redirect URI is the same for every source, so the session remembers which source the login is for.
     */
    public function getAuthorizationUrl(array $source): ?string
    {
        if (!$source['appId'] || !$source['appSecret']) {
            return null;
        }

        $state = bin2hex(random_bytes(16));
        $request = $this->requestStack->getCurrentRequest();

        $request?->getSession()->set(self::SESSION_KEY, [
            'state' => $state,
            'sourceId' => (int) $source['id'],
            'backUrl' => $request->getUri(),
        ]);

        return self::AUTHORIZATION_URL.'?'.http_build_query([
            'response_type' => 'code',
            'client_id' => $source['appId'],
            'redirect_uri' => $this->getRedirectUri(),
            'state' => $state,
            'scope' => self::SCOPE,
        ], '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * The URL LinkedIn redirects to; it has to be added to "Authorized redirect URLs for your app" (Auth tab).
     */
    public function getRedirectUri(): string
    {
        return $this->router->generate(self::ROUTE, [], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * Handles the redirect from LinkedIn: checks the state, exchanges the code for the tokens and stores them.
     *
     * @return array{sourceId: int, backUrl: string|null}
     *
     * @throws AccessTokenException
     */
    public function handleCallback(?string $code, ?string $state, ?string $error = null): array
    {
        $session = $this->requestStack->getCurrentRequest()?->getSession();
        $login = $session?->get(self::SESSION_KEY);
        $session?->remove(self::SESSION_KEY);

        if (!\is_array($login) || !$state || !hash_equals((string) $login['state'], $state)) {
            throw new AccessTokenException('The login could not be verified (invalid state). Please start it again from the import source.');
        }

        if ($error || !$code) {
            throw new AccessTokenException(sprintf('LinkedIn did not grant access%s.', $error ? ': '.$error : ''));
        }

        $source = $this->connection->fetchAssociative('SELECT * FROM tl_entity_import_source WHERE id = ?', [$login['sourceId']]);

        if (false === $source || EntityImportSourceContainer::TYPE_LINKEDIN !== $source['type']) {
            throw new AccessTokenException(sprintf('Import source ID %s is not a LinkedIn source.', $login['sourceId']));
        }

        $data = self::requestToken($this->client ?? new Client(), [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->getRedirectUri(),
            'client_id' => $source['appId'],
            'client_secret' => $source['appSecret'],
        ]);

        $this->connection->update('tl_entity_import_source', ['tstamp' => time()] + self::tokenFields($data, true), ['id' => $source['id']]);

        return ['sourceId' => (int) $source['id'], 'backUrl' => $login['backUrl'] ?? null];
    }

    /**
     * POSTs to the token endpoint (authorization code or refresh token).
     *
     * @throws AccessTokenException
     */
    public static function requestToken(ClientInterface $client, array $parameters): array
    {
        try {
            $response = $client->request('POST', self::TOKEN_URL, ['form_params' => $parameters]);
        } catch (GuzzleException $e) {
            throw AccessTokenException::fromGuzzle($e);
        }

        $data = json_decode((string) $response->getBody(), true);

        if (!\is_array($data) || empty($data['access_token'])) {
            throw new AccessTokenException('LinkedIn returned no access token.');
        }

        return $data;
    }

    /**
     * The columns to store from a token answer. A refresh keeps the refresh token and its expiry unless LinkedIn
     * sends new ones; a new login replaces them (apps without refresh tokens get none).
     */
    public static function tokenFields(array $data, bool $newLogin): array
    {
        $fields = [
            'linkedinAccessToken' => $data['access_token'],
            'accessTokenExpiration' => isset($data['expires_in']) ? (string) (time() + (int) $data['expires_in']) : '',
        ];

        if ($newLogin || isset($data['refresh_token'])) {
            $fields['linkedinRefreshToken'] = $data['refresh_token'] ?? '';
            $fields['linkedinRefreshTokenExpires'] = isset($data['refresh_token_expires_in']) ? (string) (time() + (int) $data['refresh_token_expires_in']) : '';
        }

        return $fields;
    }
}
