<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class BeforeAuthenicationEvent extends Event
{
    public const NAME = 'huh.entity_import.before_authentication_event';

    /**
     * @var array
     */
    private $auth;

    public function __construct(array $auth)
    {
        $this->auth = $auth;
    }

    public function getAuth(): array
    {
        return $this->auth;
    }

    public function setAuth(array $auth)
    {
        $this->auth = $auth;
    }
}
