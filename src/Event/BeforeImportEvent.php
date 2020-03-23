<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class BeforeImportEvent extends Event
{
    public const NAME = 'huh.entity_import.before_import_event';
    // TODO: implement event behaviour
}
