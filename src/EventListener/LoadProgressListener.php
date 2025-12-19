<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\EventListener;

use HeimrichHannot\EntityImportBundle\EventListener\DataContainer\EntityImportConfigContainer;
use HeimrichHannot\EntityImportBundle\Importer\ImporterInterface;
use HeimrichHannot\ProgressBarWidgetBundle\Event\LoadProgressEvent;
use HeimrichHannot\ProgressBarWidgetBundle\Widget\ProgressBar;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Terminal42\ServiceAnnotationBundle\Annotation\ServiceTag;

/**
 * @ServiceTag("kernel.event_listener", event="huh.progress_bar_widget.event.load_progress")
 */
class LoadProgressListener
{
    protected Request   $request;
    protected ModelUtil $modelUtil;

    public function __construct(ModelUtil $modelUtil, Request $request)
    {
        $this->request = $request;
        $this->modelUtil = $modelUtil;
    }

    public function __invoke(LoadProgressEvent $event): void
    {
        if ('tl_entity_import_quick_config' === $event->getTable()) {
            if (null === ($quickImporter = $this->modelUtil->findModelInstanceByPk('tl_entity_import_quick_config', $event->getId())) ||
                !$quickImporter->importerConfig) {
                return;
            }

            if (null === ($importConfig = $this->modelUtil->findModelInstanceByPk('tl_entity_import_config', $quickImporter->importerConfig))) {
                return;
            }
        } else {
            if (null === ($importConfig = $this->modelUtil->findModelInstanceByPk('tl_entity_import_config', $event->getId()))) {
                return;
            }
        }

        $state = match ($importConfig->state) {
            EntityImportConfigContainer::STATE_SUCCESS => ProgressBar::STATE_SUCCESS,
            EntityImportConfigContainer::STATE_FAILED => ProgressBar::STATE_FAILED,
            default => ProgressBar::STATE_IN_PROGRESS,
        };

        $data = [
            'state' => $state,
            'currentProgress' => $importConfig->importProgressCurrent,
            'totalCount' => $importConfig->importProgressTotal,
            'skippedCount' => $importConfig->importProgressSkipped,
        ];

        if ($importConfig->importProgressResult) {
            $progressBarMessages = [];
            $messages = json_decode((string) $importConfig->importProgressResult, true);

            foreach (array_reverse($messages) as $message) {
                $class = match ($message['type']) {
                    ImporterInterface::MESSAGE_TYPE_SUCCESS => 'tl_confirm',
                    ImporterInterface::MESSAGE_TYPE_ERROR => 'tl_error',
                    default => 'tl_warning',
                };

                $progressBarMessages[] = [
                    'class' => $class,
                    'text' => str_replace("\n", '<br>', $message['message']),
                ];
            }

            $data['messages'] = $progressBarMessages;
        }

        $event->setData($data);
    }
}
