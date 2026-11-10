<?php

/*
 * Copyright (c) 2026 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Token;

use Doctrine\DBAL\Connection;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use HeimrichHannot\EntityImportBundle\EventListener\DataContainer\EntityImportSourceContainer;
use HeimrichHannot\EntityImportBundle\Exception\AccessTokenException;

/**
 * Refreshes the access token of an import source and stores the new token and its expiry.
 *
 * - Instagram API with Instagram Login: a long-lived token (60 days) is exchanged for a new one once it is at least
 *   24 hours old (graph.instagram.com/refresh_access_token).
 */
class AccessTokenRefresher
{
    public const INSTAGRAM_REFRESH_URL = 'https://graph.instagram.com/refresh_access_token';

    public function __construct(
        private readonly Connection $connection,
        private readonly ?ClientInterface $client = null,
    ) {
    }

    /**
     * Whether the source (a tl_entity_import_source row) has a token this service can refresh.
     */
    public function supports(array $source): bool
    {
        return match ($source['type'] ?? '') {
            EntityImportSourceContainer::TYPE_INSTAGRAM => 'instagram' === ($source['instagramApi'] ?? '')
                && '' !== (string) ($source['instagramAccessToken'] ?? ''),
            default => false,
        };
    }

    /**
     * Refreshes the token of the source and stores the new one.
     *
     * @return int The new expiry as Unix time, 0 if the API did not tell
     *
     * @throws AccessTokenException
     */
    public function refresh(int $sourceId): int
    {
        $source = $this->connection->fetchAssociative('SELECT * FROM tl_entity_import_source WHERE id = ?', [$sourceId]);

        if (false === $source || !$this->supports($source)) {
            throw new AccessTokenException(sprintf('Import source ID %s has no access token that can be refreshed.', $sourceId));
        }

        $set = match ($source['type']) {
            EntityImportSourceContainer::TYPE_INSTAGRAM => $this->refreshInstagram($source),
        };

        $this->connection->update('tl_entity_import_source', ['tstamp' => time()] + $set, ['id' => $sourceId]);

        return (int) $set['accessTokenExpiration'];
    }

    private function refreshInstagram(array $source): array
    {
        $data = $this->requestJson('GET', self::INSTAGRAM_REFRESH_URL, [
            'query' => [
                'grant_type' => 'ig_refresh_token',
                'access_token' => $source['instagramAccessToken'],
            ],
        ]);

        if (empty($data['access_token'])) {
            throw new AccessTokenException('The Instagram API returned no access token.');
        }

        return [
            'instagramAccessToken' => $data['access_token'],
            'accessTokenExpiration' => $this->expiresAt($data),
        ];
    }

    /**
     * @throws AccessTokenException
     */
    private function requestJson(string $method, string $url, array $options): array
    {
        try {
            $response = ($this->client ?? new Client())->request($method, $url, $options);
        } catch (GuzzleException $e) {
            throw AccessTokenException::fromGuzzle($e);
        }

        $data = json_decode((string) $response->getBody(), true);

        if (!\is_array($data)) {
            throw new AccessTokenException('The API answered with invalid JSON.');
        }

        return $data;
    }

    private function expiresAt(array $data): string
    {
        return isset($data['expires_in']) ? (string) (time() + (int) $data['expires_in']) : '';
    }
}
