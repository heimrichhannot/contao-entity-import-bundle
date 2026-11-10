<?php

/*
 * Copyright (c) 2026 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Tests\EventListener\DataContainer;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\DataContainer;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use HeimrichHannot\EntityImportBundle\EventListener\DataContainer\EntityImportSourceContainer;
use HeimrichHannot\EntityImportBundle\Exception\AccessTokenException;
use HeimrichHannot\EntityImportBundle\LinkedIn\LinkedInLogin;
use HeimrichHannot\EntityImportBundle\Meta\MetaLogin;
use HeimrichHannot\EntityImportBundle\Source\SourceFactory;
use HeimrichHannot\EntityImportBundle\Token\AccessTokenRefresher;
use HeimrichHannot\EntityImportBundle\Util\EntityImportUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher\AttributesRequestMatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RefreshAccessTokenCallbackTest extends ContaoTestCase
{
    private RequestStack $requestStack;

    private Session $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->session = new Session(new MockArraySessionStorage());
        $this->requestStack = new RequestStack();

        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles', []);
        $container->set('request_stack', $this->requestStack);
        $container->set('contao.routing.scope_matcher', new ScopeMatcher(
            new AttributesRequestMatcher(['_scope' => 'backend']),
            new AttributesRequestMatcher(['_scope' => 'frontend']),
            $this->requestStack,
        ));

        System::setContainer($container);

        $GLOBALS['TL_CONFIG']['datimFormat'] = 'd.m.Y H:i';
        $GLOBALS['TL_LANG']['MSC']['entityImport']['accessTokenRefreshError'] = 'Not refreshed: %s';
        $GLOBALS['TL_LANG']['MSC']['entityImport']['accessTokenRefreshedUntil'] = 'Refreshed until %s.';
        $GLOBALS['TL_LANG']['MSC']['entityImport']['accessTokenRefreshed'] = 'Refreshed.';
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TL_CONFIG'], $GLOBALS['TL_LANG']);

        parent::tearDown();
    }

    public function testRefreshesTheTokenWhenTheCheckboxWasTicked(): void
    {
        $this->pushRequest(['refreshAccessToken' => '1']);

        $refresher = $this->createMock(AccessTokenRefresher::class);
        $refresher->expects($this->once())->method('refresh')->with(7)->willReturn(mktime(12, 0, 0, 1, 9, 2027));

        $this->createListener($refresher)->refreshAccessToken($this->mockDataContainer(7));

        $this->assertSame(['Refreshed until 09.01.2027 12:00.'], $this->session->getFlashBag()->get('contao.BE.confirm'));
    }

    public function testShowsTheEscapedErrorWhenTheRefreshFails(): void
    {
        $this->pushRequest(['refreshAccessToken' => '1']);

        $refresher = $this->createStub(AccessTokenRefresher::class);
        $refresher->method('refresh')->willThrowException(new AccessTokenException('HTTP 400: <b>expired</b>'));

        $this->createListener($refresher)->refreshAccessToken($this->mockDataContainer(7));

        $this->assertSame(['Not refreshed: HTTP 400: &lt;b&gt;expired&lt;/b&gt;'], $this->session->getFlashBag()->get('contao.BE.error'));
    }

    public function testDoesNothingWithoutTheCheckbox(): void
    {
        $this->pushRequest(['title' => 'Instagram']);

        $refresher = $this->createMock(AccessTokenRefresher::class);
        $refresher->expects($this->never())->method('refresh');

        $this->createListener($refresher)->refreshAccessToken($this->mockDataContainer(7));
    }

    public function testTheCheckboxValueIsNeverSaved(): void
    {
        $this->assertNull($this->createListener($this->createStub(AccessTokenRefresher::class))->onSaveRefreshAccessToken());
    }

    private function pushRequest(array $post): void
    {
        $request = new Request([], $post, ['_scope' => 'backend']);
        $request->setSession($this->session);

        $this->requestStack->push($request);
    }

    private function mockDataContainer(int $id): DataContainer
    {
        return $this->createClassWithPropertiesStub(DataContainer::class, ['id' => $id, 'table' => 'tl_entity_import_source']);
    }

    private function createListener(AccessTokenRefresher $refresher): EntityImportSourceContainer
    {
        return new EntityImportSourceContainer(
            $this->createStub(SourceFactory::class),
            $this->createStub(Utils::class),
            $this->createStub(Connection::class),
            $this->createStub(EntityImportUtil::class),
            $this->createStub(EventDispatcherInterface::class),
            $this->requestStack,
            $refresher,
            $this->createStub(MetaLogin::class),
            $this->createStub(LinkedInLogin::class),
        );
    }
}
