<?php

declare(strict_types=1);

/*
 * Copyright (c) 2026 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Controller;

use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\Message;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\EntityImportBundle\Exception\AccessTokenException;
use HeimrichHannot\EntityImportBundle\Meta\MetaLogin;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 * Facebook redirects here after the login started by the "request access token" link of a Facebook or Instagram
 * (Facebook Login) import source. The path is the one contao-newsroom-bundle used, so Meta apps set up for it keep
 * their "Valid OAuth Redirect URI".
 */
#[Route('/contao-newsroom/facebook/{importSource}/redirect_callback', name: MetaLogin::ROUTE, requirements: ['importSource' => '\d+'], defaults: ['_scope' => 'backend'])]
class MetaController
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly MetaLogin $metaLogin,
        private readonly Security $security,
        private readonly RouterInterface $router,
    ) {
    }

    public function __invoke(Request $request, int $importSource): Response
    {
        $this->framework->initialize();

        if (!$this->security->isGranted(ContaoCorePermissions::USER_CAN_ACCESS_MODULE, 'entityImport')) {
            throw new AccessDeniedException('Access denied');
        }

        System::loadLanguageFile('default');

        $backUrl = null;

        try {
            $backUrl = $this->metaLogin->handleCallback(
                $importSource,
                $request->query->get('code'),
                $request->query->get('state'),
                $request->query->get('error_description') ?? $request->query->get('error'),
            );

            Message::addConfirmation($GLOBALS['TL_LANG']['MSC']['entityImport']['accessTokenSavedSuccessfullyWithoutDate']);
        } catch (AccessTokenException $e) {
            Message::addError(sprintf($GLOBALS['TL_LANG']['MSC']['entityImport']['accessTokenRequestError'], StringUtil::specialchars($e->getMessage())));
        }

        return new RedirectResponse($backUrl ?? $this->router->generate('contao_backend', [
            'do' => 'entityImport',
            'act' => 'edit',
            'id' => $importSource,
        ]));
    }
}
