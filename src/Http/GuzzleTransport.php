<?php

/**
 *    ▄▄▄▄
 *  ▄█▀▀███▄▄              █▄
 *  ██    ██ ▄             ██
 *  ██    ██ ████▄▄█▀█▄ ▄████ ▄█▀█▄▀██ ██▀
 *  ██  ▄ ██ ██   ██▄█▀ ██ ██ ██▄█▀  ███
 *   ▀█████▄▄█▀  ▄▀█▄▄▄▄█▀███▄▀█▄▄▄▄██ ██▄
 *        ▀█
 *
 *  Copyright (C) 2026 — 2026, Qredex, LTD. All Rights Reserved.
 *
 *  DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 *  Licensed under the Apache License, Version 2.0. See LICENSE for the full license text.
 *  You may not use this file except in compliance with that License.
 *  Unless required by applicable law or agreed to in writing, software distributed under the
 *  License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 *  either express or implied. See the License for the specific language governing permissions
 *  and limitations under the License.
 *
 *  If you need additional information or have any questions, please email: copyright@qredex.com
 */

declare(strict_types=1);

namespace Qredex\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Qredex\Error\NetworkError;

final class GuzzleTransport implements HttpTransportInterface
{
    private Client $client;

    public function __construct(
        private readonly string $baseUrl,
        ?Client $client = null,
    ) {
        $this->client = $client ?? new Client([
            'base_uri' => rtrim($this->baseUrl, '/') . '/',
            'http_errors' => false,
        ]);
    }

    public function send(TransportRequest $request): TransportResponse
    {
        $options = [
            'headers' => $request->headers,
            'query' => array_filter($request->query, static fn (mixed $value): bool => $value !== null),
            'http_errors' => false,
            'timeout' => $request->timeoutMs / 1000,
        ];

        if ($request->bodyType === TransportRequest::BODY_JSON && is_array($request->body)) {
            $options['json'] = $request->body;
        } elseif ($request->bodyType === TransportRequest::BODY_FORM && is_array($request->body)) {
            $options['form_params'] = $request->body;
        } elseif (is_string($request->body)) {
            $options['body'] = $request->body;
        }

        try {
            $response = $this->client->request($request->method, ltrim($request->path, '/'), $options);
        } catch (GuzzleException $exception) {
            throw new NetworkError('Qredex request failed before a response was received.', previous: $exception);
        }

        /** @var array<string, string> $headers */
        $headers = [];

        foreach ($response->getHeaders() as $name => $values) {
            $headers[strtolower($name)] = implode(', ', $values);
        }

        return new TransportResponse(
            status: $response->getStatusCode(),
            headers: $headers,
            body: (string) $response->getBody(),
        );
    }
}
