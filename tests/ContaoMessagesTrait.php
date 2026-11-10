<?php

/*
 * Copyright (c) 2026 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Tests;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\System;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher\AttributesRequestMatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

/**
 * A container with a back end request and a session, so Contao\Message works and its messages can be read.
 */
trait ContaoMessagesTrait
{
    protected RequestStack $requestStack;

    protected Session $session;

    protected function setUpBackendRequest(array $post = [], array $query = [], string $uri = '/contao'): Request
    {
        $this->session = new Session(new MockArraySessionStorage());
        $this->requestStack = new RequestStack();

        $request = Request::create($uri, 'GET', $query);
        $request->request->replace($post);
        $request->attributes->set('_scope', 'backend');
        $request->setSession($this->session);
        $this->requestStack->push($request);

        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles', []);
        $container->set('request_stack', $this->requestStack);
        $container->set('contao.routing.scope_matcher', new ScopeMatcher(
            new AttributesRequestMatcher(['_scope' => 'backend']),
            new AttributesRequestMatcher(['_scope' => 'frontend']),
            $this->requestStack,
        ));

        System::setContainer($container);

        return $request;
    }

    protected function getMessages(string $type): array
    {
        return $this->session->getFlashBag()->get('contao.BE.'.$type);
    }
}
