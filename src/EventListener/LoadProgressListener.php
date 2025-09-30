<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\EventListener;

use HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer;
use HeimrichHannot\EntityImportBundle\Importer\ImporterInterface;
use HeimrichHannot\ProgressBarWidgetBundle\Event\LoadProgressEvent;
use HeimrichHannot\ProgressBarWidgetBundle\Widget\ProgressBar;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Terminal42\ServiceAnnotationBundle\Annotation\ServiceTag;

/**
 * @ServiceTag("kernel.event_listener", event="huh.progress_bar_widget.event.load_progress")
 */
class LoadProgressListener
{
    protected Utils $utils;

    public function __construct(Utils $utils)
    {
        $this->utils = $utils;
    }

    public function __invoke(LoadProgressEvent $event)
    {
        if ('tl_entity_import_quick_config' === $event->getTable()) {
            if (null === ($quickImporter = $this->utils->model()->findModelInstanceByPk('tl_entity_import_quick_config', $event->getId())) ||
                !$quickImporter->importerConfig) {
                return;
            }

            if (null === ($importConfig = $this->utils->model()->findModelInstanceByPk('tl_entity_import_config', $quickImporter->importerConfig))) {
                return;
            }
        } else {
            if (null === ($importConfig = $this->utils->model()->findModelInstanceByPk('tl_entity_import_config', $event->getId()))) {
                return;
            }
        }

        switch ($importConfig->state) {
            case EntityImportConfigContainer::STATE_SUCCESS:
                $state = ProgressBar::STATE_SUCCESS;

                break;

            case EntityImportConfigContainer::STATE_FAILED:
                $state = ProgressBar::STATE_FAILED;

                break;

            default:
                $state = ProgressBar::STATE_IN_PROGRESS;
        }

        $data = [
            'state' => $state,
            'currentProgress' => $importConfig->importProgressCurrent,
            'totalCount' => $importConfig->importProgressTotal,
            'skippedCount' => $importConfig->importProgressSkipped,
        ];

        if ($importConfig->importProgressResult) {
            $progressBarMessages = [];
            $messages = json_decode($importConfig->importProgressResult, true);

            foreach (array_reverse($messages) as $message) {
                switch ($message['type']) {
                    case ImporterInterface::MESSAGE_TYPE_SUCCESS:
                        $class = 'tl_confirm';

                        break;

                    case ImporterInterface::MESSAGE_TYPE_ERROR:
                        $class = 'tl_error';

                        break;

                    default:
                        $class = 'tl_warning';
                }

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
