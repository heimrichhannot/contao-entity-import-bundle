<?php

/*
 * Copyright (c) 2026 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Meta;

use Doctrine\DBAL\Connection;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use HeimrichHannot\EntityImportBundle\EventListener\DataContainer\EntityImportSourceContainer;
use HeimrichHannot\EntityImportBundle\Exception\AccessTokenException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\Facebook;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * "Facebook Login" for import sources that read a Facebook page or, with the Instagram API with Facebook Login, the
 * Instagram account connected to a page.
 *
 * The user logs in with Facebook and grants the app access to their pages; the long-lived user token is then
 * exchanged for the page's access token, which does not expire (it stops working if the user loses their role on
 * the page, changes their password or removes the app). That page token is stored:
 *
 * - Facebook sources: as facebookAccessToken, for the page in facebookPageId (or the user's only page)
 * - Instagram sources: as instagramAccessToken, for the page connected to instagramUserID (or the user's only
 *   page with an Instagram professional account); the Instagram Graph API accepts page tokens
 */
class MetaLogin
{
    public const ROUTE = 'contao_newsroom_facebook_redirect_callback';

    private const SESSION_KEY = 'huh_entity_import_meta_login';

    private const SCOPES = [
        EntityImportSourceContainer::TYPE_FACEBOOK => ['pages_show_list', 'pages_read_engagement'],
        EntityImportSourceContainer::TYPE_INSTAGRAM => ['pages_show_list', 'pages_read_engagement', 'instagram_basic'],
    ];

    public function __construct(
        private readonly Connection $connection,
        private readonly RequestStack $requestStack,
        private readonly RouterInterface $router,
        private readonly ?ClientInterface $client = null,
    ) {
    }

    /**
     * The Facebook login URL for the source (a tl_entity_import_source row), or null if the source lacks the app
     * credentials. Remembers the OAuth state and the current back end URL to return to.
     *
     * @throws AccessTokenException
     */
    public function getAuthorizationUrl(array $source): ?string
    {
        if (!$source['appId'] || !$source['appSecret'] || !$source['metaApiVersion'] || !isset(self::SCOPES[$source['type']])) {
            return null;
        }

        $provider = $this->createProvider($source);
        $url = $provider->getAuthorizationUrl(['scope' => self::SCOPES[$source['type']]]);

        $request = $this->requestStack->getCurrentRequest();
        $request?->getSession()->set(self::SESSION_KEY.'.'.$source['id'], [
            'state' => $provider->getState(),
            'backUrl' => $request->getUri(),
        ]);

        return $url;
    }

    /**
     * The URL Facebook redirects to; it has to be entered as "Valid OAuth Redirect URI" in the app's settings.
     */
    public function getRedirectUri(int $sourceId): string
    {
        $uri = $this->router->generate(self::ROUTE, ['importSource' => $sourceId], UrlGeneratorInterface::ABSOLUTE_URL);

        // Facebook accepts https redirect URIs only
        return preg_replace('#^http://#', 'https://', $uri);
    }

    /**
     * Handles the redirect from Facebook: checks the state, gets the page token and stores it.
     *
     * @return string|null The back end URL the login was started from
     *
     * @throws AccessTokenException
     */
    public function handleCallback(int $sourceId, ?string $code, ?string $state, ?string $error = null): ?string
    {
        $session = $this->requestStack->getCurrentRequest()?->getSession();
        $login = $session?->get(self::SESSION_KEY.'.'.$sourceId);
        $session?->remove(self::SESSION_KEY.'.'.$sourceId);

        if (!\is_array($login) || !$state || !hash_equals((string) $login['state'], $state)) {
            throw new AccessTokenException('The login could not be verified (invalid state). Please start it again from the import source.');
        }

        if ($error || !$code) {
            throw new AccessTokenException(sprintf('Facebook did not grant access%s.', $error ? ': '.$error : ''));
        }

        $source = $this->connection->fetchAssociative('SELECT * FROM tl_entity_import_source WHERE id = ?', [$sourceId]);

        if (false === $source || !isset(self::SCOPES[$source['type']])) {
            throw new AccessTokenException(sprintf('Import source ID %s is not a Facebook or Instagram source.', $sourceId));
        }

        $provider = $this->createProvider($source);

        try {
            $userToken = $provider->getAccessToken('authorization_code', ['code' => $code]);
            $userToken = $provider->getLongLivedAccessToken($userToken->getToken());
        } catch (IdentityProviderException $e) {
            throw new AccessTokenException('Facebook refused the access token: '.$e->getMessage(), 0, $e);
        } catch (GuzzleException $e) {
            throw AccessTokenException::fromGuzzle($e);
        }

        $pages = $this->getPages($provider, $source, $userToken->getToken());

        $set = EntityImportSourceContainer::TYPE_FACEBOOK === $source['type']
            ? $this->selectFacebookPage($source, $pages)
            : $this->selectInstagramAccount($source, $pages);

        // page tokens derived from a long-lived user token do not expire
        $this->connection->update('tl_entity_import_source', $set + ['tstamp' => time(), 'accessTokenExpiration' => ''], ['id' => $sourceId]);

        return $login['backUrl'] ?? null;
    }

    private function getPages(Facebook $provider, array $source, string $userToken): array
    {
        $url = sprintf('https://graph.facebook.com/%s/me/accounts?', $source['metaApiVersion']).http_build_query([
            'fields' => 'id,name,access_token,instagram_business_account{id,username}',
            'limit' => 100,
            'access_token' => $userToken,
            'appsecret_proof' => hash_hmac('sha256', $userToken, $source['appSecret']),
        ]);

        try {
            $result = $provider->getParsedResponse($provider->getRequest('GET', $url));
        } catch (IdentityProviderException $e) {
            throw new AccessTokenException('The pages could not be read: '.$e->getMessage(), 0, $e);
        } catch (GuzzleException $e) {
            throw AccessTokenException::fromGuzzle($e);
        }

        return array_values(array_filter($result['data'] ?? [], static fn ($page): bool => \is_array($page) && !empty($page['access_token'])));
    }

    private function selectFacebookPage(array $source, array $pages): array
    {
        $candidates = $source['facebookPageId']
            ? array_filter($pages, static fn (array $page): bool => (string) $page['id'] === (string) $source['facebookPageId'])
            : $pages;

        if (1 !== \count($candidates)) {
            throw new AccessTokenException(($source['facebookPageId'] ? 'The page ID is not one of your pages.' : 'Please enter the ID of the page to import.').self::listPages($pages, false));
        }

        $page = reset($candidates);

        return ['facebookPageId' => (string) $page['id'], 'facebookAccessToken' => $page['access_token']];
    }

    private function selectInstagramAccount(array $source, array $pages): array
    {
        $pages = array_filter($pages, static fn (array $page): bool => !empty($page['instagram_business_account']['id']));

        $candidates = $source['instagramUserID']
            ? array_filter($pages, static fn (array $page): bool => (string) $page['instagram_business_account']['id'] === (string) $source['instagramUserID'])
            : $pages;

        if (1 !== \count($candidates)) {
            throw new AccessTokenException(($source['instagramUserID'] ? 'The Instagram user ID is not connected to one of your pages.' : 'Please enter the ID of the Instagram account to import.').self::listPages($pages, true));
        }

        $page = reset($candidates);

        return ['instagramUserID' => (string) $page['instagram_business_account']['id'], 'instagramAccessToken' => $page['access_token']];
    }

    private static function listPages(array $pages, bool $instagram): string
    {
        if (!$pages) {
            return $instagram ? ' None of your pages has an Instagram professional account.' : ' You do not manage any page the app was granted.';
        }

        return ' Available: '.implode(', ', array_map(static fn (array $page): string => $instagram
            ? sprintf('@%s (ID %s, page "%s")', $page['instagram_business_account']['username'] ?? '?', $page['instagram_business_account']['id'], $page['name'] ?? '')
            : sprintf('"%s" (ID %s)', $page['name'] ?? '', $page['id']), $pages));
    }

    /**
     * @throws AccessTokenException
     */
    private function createProvider(array $source): Facebook
    {
        if (!preg_match('/^v\d+\.\d+$/', (string) $source['metaApiVersion'])) {
            throw new AccessTokenException(sprintf('The Graph API version "%s" is invalid; it looks like "v24.0".', $source['metaApiVersion']));
        }

        return new Facebook(
            [
                'clientId' => $source['appId'],
                'clientSecret' => $source['appSecret'],
                'graphApiVersion' => $source['metaApiVersion'],
                'redirectUri' => $this->getRedirectUri((int) $source['id']),
            ],
            $this->client ? ['httpClient' => $this->client] : [],
        );
    }
}
