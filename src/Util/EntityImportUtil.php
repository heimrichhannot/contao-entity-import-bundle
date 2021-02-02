<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Util;

use Contao\Controller;
use Contao\Email;
use Contao\Message;
use HeimrichHannot\EntityImportBundle\Model\EntityImportConfigModel;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;
use Psr\Log\LogLevel;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EntityImportUtil
{
    /**
     * @var ContainerUtil
     */
    protected $containerUtil;
    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var DatabaseUtil
     */
    protected $databaseUtil;
    /**
     * @var UrlUtil
     */
    protected $urlUtil;

    /**
     * @var string
     */
    protected $exception = '';
    /**
     * @var array
     */
    protected $debugConfig = [];

    public function __construct(ContainerInterface $container, ContainerUtil $containerUtil, DatabaseUtil $databaseUtil, UrlUtil $urlUtil)
    {
        $this->containerUtil = $containerUtil;
        $this->container = $container;
        $this->databaseUtil = $databaseUtil;
        $this->urlUtil = $urlUtil;
    }

    public function transformFieldMappingSourceValueToSelect($options)
    {
        $dca = &$GLOBALS['TL_DCA']['tl_entity_import_source']['fields']['fieldMapping']['eval']['multiColumnEditor']['fields']['sourceValue'];

        $dca['inputType'] = 'select';
        $dca['options'] = $options;
        $dca['eval']['includeBlankOption'] = true;
        $dca['eval']['mandatory'] = true;
        $dca['eval']['chosen'] = true;
    }

    public function handleSourceConfigError(string $message): void
    {
        $this->initErrorHandling($message);

        if ($this->containerUtil->isBackend()) {
            $this->handleDebugMessageForBackend();
        }
    }

    public function handleImportError(string $message, EntityImportConfigModel $importConfig): void
    {
        $this->initErrorHandling($message);

        if ($this->containerUtil->isBackend()) {
            $this->handleDebugMessageForBackend();
            $this->reloadPageWithoutAction();
        } else {
            $this->handleDebugMessageForFrontend($importConfig);

            exit();
        }
    }

    /**
     * set up protected values and log action.
     */
    protected function initErrorHandling(string $message): void
    {
        $this->setProperties($message);
        $this->logError();
    }

    protected function setProperties(string $message): void
    {
        $this->exception = $message;
        $this->debugConfig = $this->getDebugConfig();
    }

    protected function handleDebugMessageForFrontend(EntityImportConfigModel $importConfig): void
    {
        if ($importConfig->errorNotificationLock) {
            return;
        }

        if (isset($this->debugConfig['email']) && $this->debugConfig['email']) {
            Controller::loadLanguageFile('default');
            $content = sprintf('An error occurred on domain "%s"', $importConfig->cronDomain).' : '.$this->exception;
            $email = new Email();
            $email->html = $content;
            $email->text = strip_tags($content);
            $email->subject = sprintf($GLOBALS['TL_LANG']['MSC']['entityImport']['exceptionEmailSubject'], $importConfig->title, $importConfig->id);
            $email->sendTo($importConfig->errorNotificationEmail ?: $GLOBALS['TL_CONFIG']['adminEmail']);
        }

        $this->databaseUtil->update('tl_entity_import_config', ['errorNotificationLock' => '1'], 'tl_entity_import_config.id=?', [$importConfig->id]);
    }

    /**
     * add the error message to display in backend.
     */
    protected function handleDebugMessageForBackend(): void
    {
        Message::addError($this->exception);
    }

    protected function getDebugConfig(): ?array
    {
        $config = $this->container->getParameter('huh_entity_import');

        return $config['debug'];
    }

    /**
     * if set in the config log the error.
     */
    protected function logError(): void
    {
        if (isset($this->debugConfig['contao_log']) && $this->debugConfig['contao_log']) {
            $this->containerUtil->log($this->exception, 'executeImport', LogLevel::ERROR);
        }
    }

    /**
     * reload if import was initialized from backend.
     */
    protected function reloadPageWithoutAction(): void
    {
        // check if key is set
        // if not do not redirect -> would cause redirect loop
        $url = $this->urlUtil->getCurrentUrl();

        if (false === strpos($url, 'key=import')) {
            return;
        }

        $this->urlUtil->redirect($this->urlUtil->removeQueryString(['key']));
    }
}
