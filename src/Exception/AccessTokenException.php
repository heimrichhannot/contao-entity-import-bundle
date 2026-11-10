<?php

/*
 * Copyright (c) 2026 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Exception;

/**
 * An access token could not be requested or refreshed. The message is safe to show in the back end: it never
 * contains the token (Guzzle's own messages do, since the token is part of the request URL).
 */
class AccessTokenException extends \RuntimeException
{
}
