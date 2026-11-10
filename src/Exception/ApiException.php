<?php

/*
 * Copyright (c) 2026 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Exception;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

/**
 * A request to a platform's API failed. The message is safe to show in the back end and to log: it never contains
 * the request URL, which may hold an access token or API key (Guzzle's own messages do).
 */
class ApiException extends \RuntimeException
{
    public static function fromGuzzle(GuzzleException $e): static
    {
        if (!$e instanceof RequestException || null === ($response = $e->getResponse())) {
            return new static('The API could not be reached.', 0, $e);
        }

        // Graph API: error.message, Google: error.message, OAuth 2: error_description, LinkedIn: message
        $data = json_decode((string) $response->getBody(), true);
        $message = $data['error']['message'] ?? $data['error_description'] ?? $data['message'] ?? null;

        return new static(sprintf('HTTP %s%s', $response->getStatusCode(), \is_string($message) ? ': '.$message : ''), 0, $e);
    }
}
