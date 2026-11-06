<?php

/*
 * Copyright (c) 2026 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Cron;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCronJob;
use Doctrine\DBAL\Connection;
use GuzzleHttp\Client;
use HeimrichHannot\EntityImportBundle\EventListener\DataContainer\EntityImportSourceContainer;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Long-lived tokens of the Instagram API with Instagram Login expire after 60 days and can be refreshed once they
 * are at least 24 hours old. Refreshes every such token that expires within the next 30 days (or whose expiry is
 * unknown), so an import source keeps working without a new login.
 */
#[AsCronJob('daily')]
class InstagramTokenRefreshCron
{
    public const REFRESH_URL = 'https://graph.instagram.com/refresh_access_token?grant_type=ig_refresh_token&access_token=%s';

    private const REFRESH_BEFORE = 30 * 86400;

    public function __construct(
        private readonly Connection $connection,
        #[Autowire(service: 'monolog.logger.contao.error')]
        private readonly LoggerInterface $contaoErrorLogger,
        private readonly ?Client $client = null,
    ) {
    }

    public function __invoke(): void
    {
        $sources = $this->connection->fetchAllAssociative(
            "SELECT id, instagramAccessToken, accessTokenExpiration FROM tl_entity_import_source WHERE type = ? AND instagramApi = 'instagram' AND instagramAccessToken != ''",
            [EntityImportSourceContainer::TYPE_INSTAGRAM],
        );

        foreach ($sources as $source) {
            if ((int) $source['accessTokenExpiration'] > time() + self::REFRESH_BEFORE) {
                continue;
            }

            try {
                $response = ($this->client ?? new Client())->request('get', sprintf(self::REFRESH_URL, $source['instagramAccessToken']));
                $data = json_decode($response->getBody()->getContents(), true);
            } catch (\Throwable $e) {
                $this->contaoErrorLogger->error(sprintf('Entity import source ID %s: the Instagram access token could not be refreshed (%s).', $source['id'], $e->getMessage()));

                continue;
            }

            if (empty($data['access_token'])) {
                $this->contaoErrorLogger->error(sprintf('Entity import source ID %s: the Instagram token refresh returned no access token.', $source['id']));

                continue;
            }

            $this->connection->update('tl_entity_import_source', [
                'tstamp' => time(),
                'instagramAccessToken' => $data['access_token'],
                'accessTokenExpiration' => (string) (time() + (int) ($data['expires_in'] ?? 0)),
            ], ['id' => $source['id']]);
        }
    }
}
