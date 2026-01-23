<?php

declare(strict_types=1);

namespace HeimrichHannot\EntityImportBundle\Controller;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Controller\AbstractController;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Environment;
use Contao\Message;
use Doctrine\DBAL\Connection;
use HeimrichHannot\NewsroomBundle\Source\FacebookSource;
use HeimrichHannot\UtilsBundle\Util\Utils;
use League\OAuth2\Client\Provider\Facebook;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment as TwigEnvironment;

#[Route('/contao-newsroom/facebook/{importSource}/redirect_callback', name: 'contao_newsroom_facebook_redirect_callback', defaults: ['_scope' => 'backend', '_token_check' => true])]
class MetaController extends AbstractController
{
    public function __construct(
        private readonly Utils $utils,
        private readonly ContaoFramework $framework,
        private readonly TwigEnvironment $twig,
        private readonly Connection $connection,
        private readonly RouterInterface $router,
        private readonly Security $security,
        private readonly LoggerInterface $logger
    ) {
    }

    public function __invoke(Request $request, string $importSource): Response
    {
        $this->framework->initialize();

        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedException('Access denied');
        }

        Controller::loadLanguageFile('default');

        if (null === ($sourceModel = $this->utils->model()->findModelInstanceByPk('tl_entity_import_source', $importSource))) {
            Message::addError(
                sprintf($GLOBALS['TL_LANG']['MSC']['newsroom']['entityImportSourceNotFound'], $importSource)
            );

            return $this->renderTemplate();
        }

        $redirectUri = Environment::get('url').$this->router->generate('contao_newsroom_facebook_redirect_callback', [
                'importSource' => $sourceModel->id,
            ]);

        if (str_starts_with($redirectUri, 'http://')) {
            $redirectUri = 'https://'.substr($redirectUri, 7);
        }

        if (!str_starts_with($redirectUri, 'http') && !str_starts_with($redirectUri, 'https')) {
            $redirectUri = Environment::get('url').'/'.$redirectUri;
        }

        try {
            $facebook = new Facebook([
                'clientId' => $sourceModel->appId,
                'clientSecret' => $sourceModel->appSecret,
                'redirectUri' => $redirectUri,
                'graphApiVersion' => FacebookSource::GRAPH_VERSION,
            ]);
        } catch (\Exception $e) {
            Message::addError('Facebook error: '.$e->getMessage());
            $this->logger->error('Facebook initialization error', [
                'exception' => $e->getMessage(),
                'contao' => new ContaoContext(__METHOD__, ContaoContext::ERROR),
            ]);

            return $this->renderTemplate();
        }

        try {
            $accessToken = $facebook->getAccessToken('authorization_code', [
                'code' => $request->query->get('code'),
            ]);
        } catch (\Exception $e) {
            Message::addError('Facebook SDK returned an error: '.$e->getMessage());

            return $this->renderTemplate();
        }

        if (!$accessToken) {
            Message::addError('Error getting short-life access token');

            return $this->renderTemplate();
        }

        try {
            $accessToken = $facebook->getLongLivedAccessToken($accessToken->getToken());
        } catch (\Exception $e) {
            Message::addError('Error getting long-lived access token: '.$e->getMessage());

            return $this->renderTemplate();
        }

        $accessTokenString = $accessToken->getToken();
        $expiresAt = $accessToken->getExpires();

        $this->connection->update('tl_entity_import_source', [
            'facebookAccessToken' => $accessTokenString,
            'accessTokenExpiration' => $expiresAt,
        ], ['id' => $sourceModel->id]);

        Message::addConfirmation(
            sprintf($GLOBALS['TL_LANG']['MSC']['newsroom']['accessTokenSavedSuccessfully'], date(Config::get('datimFormat'), $expiresAt))
        );

        return $this->renderTemplate();
    }

    private function renderTemplate(): Response
    {
        return new Response($this->twig->render('@HeimrichHannotNewsroom/facebook/newsroom_facebook_redirect_callback.html.twig', [
            'statusMessages' => Message::generate(),
        ]));
    }
}
