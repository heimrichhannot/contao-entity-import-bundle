<?php

/*
 * Copyright (c) 2026 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Tests\Cron;

use Doctrine\DBAL\Connection;
use HeimrichHannot\EntityImportBundle\Cron\AccessTokenRefreshCron;
use HeimrichHannot\EntityImportBundle\Exception\AccessTokenException;
use HeimrichHannot\EntityImportBundle\Token\AccessTokenRefresher;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class AccessTokenRefreshCronTest extends TestCase
{
    public function testRefreshesTokensThatExpireWithin30DaysOrWhoseExpiryIsUnknown(): void
    {
        $connection = $this->createStub(Connection::class);
        $connection->method('fetchAllAssociative')->willReturn([
            ['id' => 1, 'accessTokenExpiration' => (string) (time() + 10 * 86400)],
            ['id' => 2, 'accessTokenExpiration' => (string) (time() + 50 * 86400)],
            ['id' => 3, 'accessTokenExpiration' => ''],
            ['id' => 4, 'accessTokenExpiration' => '', 'unsupported' => true],
            ['id' => 5, 'accessTokenExpiration' => ''],
        ]);

        $refresher = $this->createStub(AccessTokenRefresher::class);
        $refresher->method('supports')->willReturnCallback(static fn (array $source): bool => !isset($source['unsupported']));

        $refreshed = [];
        $refresher
            ->method('refresh')
            ->willReturnCallback(static function (int $id) use (&$refreshed): int {
                $refreshed[] = $id;

                if (5 === $id) {
                    throw new AccessTokenException('HTTP 400: Session has expired');
                }

                return time() + 5184000;
            })
        ;

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('error')
            ->with('Entity import source ID 5: the access token could not be refreshed (HTTP 400: Session has expired).')
        ;

        (new AccessTokenRefreshCron($connection, $refresher, $logger))();

        $this->assertSame([1, 3, 5], $refreshed);
    }

    public function testWarnsWhenTheLinkedInRefreshTokenRunsOut(): void
    {
        $expires = time() + 10 * 86400;

        $connection = $this->createStub(Connection::class);
        $connection->method('fetchAllAssociative')->willReturn([
            ['id' => 9, 'type' => 'linkedin', 'accessTokenExpiration' => (string) (time() + 50 * 86400), 'linkedinRefreshTokenExpires' => (string) $expires],
            ['id' => 10, 'type' => 'linkedin', 'accessTokenExpiration' => '', 'linkedinRefreshTokenExpires' => (string) (time() + 300 * 86400)],
        ]);

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('error')
            ->with(sprintf('Entity import source ID 9: the LinkedIn refresh token is valid until %s only; please request a new access token in the import source.', date('Y-m-d', $expires)))
        ;

        (new AccessTokenRefreshCron($connection, $this->createStub(AccessTokenRefresher::class), $logger))();
    }
}
