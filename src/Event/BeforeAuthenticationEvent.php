<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Event;

use Contao\Model;
use Symfony\Component\EventDispatcher\Event;

class BeforeAuthenticationEvent extends Event
{
    public const NAME = 'huh.entity_import.before_authentication_event';

    private array $auth;
    private Model $sourceModel;

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

    public function getSourceModel()
    {
        return $this->sourceModel;
    }

    public function setSourceModel(Model $sourceModel)
    {
        $this->sourceModel = $sourceModel;
    }
}
