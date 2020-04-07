<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\EventListener;

use HeimrichHannot\EntityImportBundle\Event\BeforeImportEvent;

class BeforeImportEventListener
{
    public function onHuhEntityImportBeforeImportEvent(BeforeImportEvent $event)
    {
        $items = $event->getItems();
        $test = 'test';
        $event->setItems($items);
    }
}
