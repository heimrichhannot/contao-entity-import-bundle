<?php

/*
 * Copyright (c) 2026 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Cron;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCronJob;
use Doctrine\DBAL\Connection;
use HeimrichHannot\EntityImportBundle\Exception\AccessTokenException;
use HeimrichHannot\EntityImportBundle\Token\AccessTokenRefresher;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Refreshes every access token that AccessTokenRefresher supports and that expires within the next 30 days (or
 * whose expiry is unknown), so an import source keeps working without a new login.
 */
#[AsCronJob('daily')]
class AccessTokenRefreshCron
{
    private const REFRESH_BEFORE = 30 * 86400;

    public function __construct(
        private readonly Connection $connection,
        private readonly AccessTokenRefresher $refresher,
        #[Autowire(service: 'monolog.logger.contao.error')]
        private readonly LoggerInterface $contaoErrorLogger,
    ) {
    }

    public function __invoke(): void
    {
        foreach ($this->connection->fetchAllAssociative('SELECT * FROM tl_entity_import_source') as $source) {
            if (!$this->refresher->supports($source) || (int) $source['accessTokenExpiration'] > time() + self::REFRESH_BEFORE) {
                continue;
            }

            try {
                $this->refresher->refresh((int) $source['id']);
            } catch (AccessTokenException $e) {
                $this->contaoErrorLogger->error(sprintf('Entity import source ID %s: the access token could not be refreshed (%s).', $source['id'], $e->getMessage()));
            }
        }
    }
}
