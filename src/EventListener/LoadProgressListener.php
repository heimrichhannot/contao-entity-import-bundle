<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\EventListener;

use HeimrichHannot\EntityImportBundle\DataContainer\EntityImportConfigContainer;
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

    public function __invoke(LoadProgressEvent $event)
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

        switch ($importConfig->state) {
            case EntityImportConfigContainer::STATE_SUCCESS:
                $state = ProgressBar::STATE_SUCCESS;
                $class = 'tl_confirm';

                break;

            case EntityImportConfigContainer::STATE_FAILED:
                $state = ProgressBar::STATE_FAILED;
                $class = 'tl_error';

                break;

            default:
                $state = ProgressBar::STATE_IN_PROGRESS;
                $class = '';
        }

        $data = [
            'state' => $state,
            'currentProgress' => $importConfig->importProgressCurrent,
            'totalCount' => $importConfig->importProgressTotal,
            'skippedCount' => $importConfig->importProgressSkipped,
        ];

        if ($importConfig->importProgressResult) {
            $data['messages'] = [[
                'class' => $class,
                'text' => str_replace("\n", '<br>', $importConfig->importProgressResult),
            ]];
        }

        $event->setData($data);
    }
}
