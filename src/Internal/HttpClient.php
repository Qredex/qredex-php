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

namespace Qredex\Internal;

use Closure;
use Psr\Log\LoggerInterface;
use Qredex\Config\RetryPolicy;
use Qredex\Error\NetworkError;
use Qredex\Error\QredexError;
use Qredex\Error\ResponseDecodingError;
use Qredex\Http\HttpTransportInterface;
use Qredex\Http\TransportRequest;
use Qredex\Http\TransportResponse;

final class HttpClient
{
    /**
     * @param array<string, string> $defaultHeaders
     */
    public function __construct(
        private readonly HttpTransportInterface $transport,
        private readonly TokenProvider $tokenProvider,
        private readonly int $timeoutMs,
        private readonly array $defaultHeaders,
        private readonly EventEmitter $events,
        private readonly ?LoggerInterface $logger = null,
        private readonly ?RetryPolicy $readRetry = null,
        private readonly ?Closure $requestIdFactory = null,
        private readonly ?string $requestIdHeader = null,
    ) {
    }

    /**
     * @param array<string, scalar|null> $query
     * @param array<string, mixed>|null $body
     */
    public function json(string $method, string $path, array $query = [], ?array $body = null): array
    {
        $attempts = $this->readRetry !== null && in_array(strtoupper($method), ['GET', 'HEAD'], true)
            ? $this->readRetry->maxAttempts
            : 1;
        $lastError = null;

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                $response = $this->send($method, $path, $query, $body);

                return $this->decode($response);
            } catch (QredexError $error) {
                $lastError = $error;

                if ($this->readRetry === null || $attempt >= $attempts || !$this->shouldRetry($error)) {
                    throw $error;
                }

                $delay = Retry::delayMs($this->readRetry, $attempt, $error->retryAfterSeconds);
                $this->events->emit('retry_scheduled', [
                    'attempt' => $attempt,
                    'delay_ms' => $delay,
                    'max_attempts' => $this->readRetry->maxAttempts,
                    'method' => strtoupper($method),
                    'path' => $path,
                    'retry_after_seconds' => $error->retryAfterSeconds,
                    'source' => 'read',
                ]);
                Retry::sleep($delay);
            }
        }

        throw $lastError ?? new QredexError('Qredex request failed unexpectedly.');
    }

    /**
     * @param array<string, scalar|null> $query
     * @param array<string, mixed>|null $body
     */
    private function send(string $method, string $path, array $query, ?array $body): TransportResponse
    {
        $clientRequestId = $this->newRequestId();
        $headers = $this->defaultHeaders + [
            'accept' => 'application/json',
            'user-agent' => $this->defaultHeaders['user-agent'] ?? 'qredex-php/unknown',
            'authorization' => $this->tokenProvider->authorizationHeader(),
        ];

        if ($this->requestIdHeader !== null) {
            $headers[$this->requestIdHeader] = $clientRequestId;
        }

        if ($body !== null) {
            $headers['content-type'] = 'application/json';
        }

        $this->events->emit('request', [
            'client_request_id' => $clientRequestId,
            'method' => strtoupper($method),
            'path' => $path,
        ]);

        $this->logger?->debug('qredex.request', [
            'client_request_id' => $clientRequestId,
            'method' => strtoupper($method),
            'path' => $path,
        ]);

        $response = $this->transport->send(new TransportRequest(
            method: strtoupper($method),
            path: $path,
            headers: $headers,
            query: $query,
            body: $body,
            bodyType: $body === null ? TransportRequest::BODY_NONE : TransportRequest::BODY_JSON,
            timeoutMs: $this->timeoutMs,
        ));

        if ($response->status < 200 || $response->status >= 300) {
            throw ErrorFactory::fromResponse($response);
        }

        $this->events->emit('response', [
            'client_request_id' => $clientRequestId,
            'method' => strtoupper($method),
            'path' => $path,
            'status' => $response->status,
            'request_id' => $response->header('x-request-id'),
            'trace_id' => $response->header('x-trace-id'),
        ]);

        $this->logger?->info('qredex.response', [
            'client_request_id' => $clientRequestId,
            'method' => strtoupper($method),
            'path' => $path,
            'status' => $response->status,
            'request_id' => $response->header('x-request-id'),
            'trace_id' => $response->header('x-trace-id'),
        ]);

        return $response;
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(TransportResponse $response): array
    {
        if ($response->body === '') {
            return [];
        }

        $decoded = json_decode($response->body, true);

        if (!is_array($decoded)) {
            throw new ResponseDecodingError(
                'Qredex returned a non-JSON response.',
                status: $response->status,
                requestId: $response->header('x-request-id'),
                traceId: $response->header('x-trace-id'),
                responseText: $response->body,
            );
        }

        return $decoded;
    }

    private function shouldRetry(QredexError $error): bool
    {
        if ($error instanceof NetworkError) {
            return true;
        }

        return $error->status !== null && Retry::shouldRetryStatus($error->status);
    }

    private function newRequestId(): string
    {
        try {
            $requestId = $this->requestIdFactory !== null ? ($this->requestIdFactory)() : bin2hex(random_bytes(16));
        } catch (\Throwable) {
            return uniqid('qdx_', true);
        }

        if (!is_string($requestId) || trim($requestId) === '') {
            return uniqid('qdx_', true);
        }

        return $requestId;
    }
}
