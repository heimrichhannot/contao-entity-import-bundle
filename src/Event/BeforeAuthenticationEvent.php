<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Event;

use Contao\Model;
use Symfony\Component\EventDispatcher\Event;

class BeforeAuthenticationEvent extends Event
{
    public const NAME = 'huh.entity_import.before_authentication_event';

    /**
     * @var array
     */
    private $auth;
    /**
     * @var Model
     */
    private $sourceModel;

    /**
     * BeforeAuthenticationEvent constructor.
     */
    public function __construct(array $auth, Model $sourceModel)
    {
        $this->auth = $auth;
        $this->sourceModel = $sourceModel;
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
