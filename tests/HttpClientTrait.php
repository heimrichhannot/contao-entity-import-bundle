<?php

/*
 * Copyright (c) 2026 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EntityImportBundle\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;

/**
 * A Guzzle client that answers with the given responses and records the requests in $this->history.
 */
trait HttpClientTrait
{
    protected array $history = [];

    protected function mockHttpClient(array $responses): Client
    {
        $this->history = [];

        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($this->history));

        return new Client(['handler' => $stack]);
    }

    /**
     * The query parameters of the request with the given index.
     */
    protected function requestQuery(int $index = 0): array
    {
        parse_str($this->history[$index]['request']->getUri()->getQuery(), $query);

        return $query;
    }
}
