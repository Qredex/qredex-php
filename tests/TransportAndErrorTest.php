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

namespace Qredex\Tests;

use PHPUnit\Framework\TestCase;
use Qredex\Auth\ClientCredentialsAuthentication;
use Qredex\Auth\QredexScope;
use Qredex\Config\QredexConfig;
use Qredex\Config\RetryPolicy;
use Qredex\Error\ResponseDecodingError;
use Qredex\Http\TransportResponse;
use Qredex\Qredex;

final class TransportAndErrorTest extends TestCase
{
    public function testReadRetriesHonorRetryAfterAndEmitClientRequestId(): void
    {
        $events = [];
        $transport = new FakeTransport();
        $transport->push(new TransportResponse(200, [], json_encode([
            'access_token' => 'cached-token',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ], JSON_THROW_ON_ERROR)));
        $transport->push(new TransportResponse(429, [
            'retry-after' => '3',
        ], json_encode([
            'error_code' => 'rate_limited',
            'message' => 'Slow down.',
        ], JSON_THROW_ON_ERROR)));
        $transport->push(new TransportResponse(200, [], json_encode([
            'items' => [],
            'page' => 0,
            'size' => 25,
            'total_elements' => 0,
            'total_pages' => 0,
        ], JSON_THROW_ON_ERROR)));

        $sdk = Qredex::init(new QredexConfig(
            auth: new ClientCredentialsAuthentication('client-id', 'client-secret', QredexScope::ORDERS_READ),
            transport: $transport,
            readRetry: new RetryPolicy(maxAttempts: 2, baseDelayMs: 0, maxDelayMs: 0, useJitter: false),
            eventListener: static function (array $event) use (&$events): void {
                $events[] = $event;
            },
            requestIdFactory: static fn (): string => 'client-request-123',
            requestIdHeader: 'x-client-request-id',
        ));

        $sdk->orders()->list();

        $requestEvents = array_values(array_filter($events, static fn (array $event): bool => ($event['type'] ?? null) === 'request'));
        $retryEvents = array_values(array_filter($events, static fn (array $event): bool => ($event['type'] ?? null) === 'retry_scheduled'));

        self::assertCount(3, $transport->requests);
        self::assertSame('client-request-123', $transport->requests[1]->headers['x-client-request-id']);
        self::assertSame('client-request-123', $transport->requests[2]->headers['x-client-request-id']);
        self::assertCount(2, $requestEvents);
        self::assertCount(1, $retryEvents);
        self::assertSame('client-request-123', $requestEvents[0]['client_request_id']);
        self::assertSame(3000, $retryEvents[0]['delay_ms']);
        self::assertSame(3, $retryEvents[0]['retry_after_seconds']);
    }

    public function testNetworkErrorsRetryOnReads(): void
    {
        $transport = new FakeTransport();
        $transport->push(new TransportResponse(200, [], json_encode([
            'access_token' => 'cached-token',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ], JSON_THROW_ON_ERROR)));
        $transport->push(new \RuntimeException('temporary network issue'));
        $transport->push(new TransportResponse(200, [], json_encode([
            'items' => [],
            'page' => 0,
            'size' => 25,
            'total_elements' => 0,
            'total_pages' => 0,
        ], JSON_THROW_ON_ERROR)));

        $sdk = Qredex::init(new QredexConfig(
            auth: new ClientCredentialsAuthentication('client-id', 'client-secret', QredexScope::ORDERS_READ),
            transport: $transport,
            readRetry: new RetryPolicy(maxAttempts: 2, baseDelayMs: 0, maxDelayMs: 0, useJitter: false),
        ));

        $orders = $sdk->orders()->list();

        self::assertSame(0, $orders->totalElements);
        self::assertCount(3, $transport->requests);
    }

    public function testMalformedResponseIsReportedAsResponseDecodingError(): void
    {
        $transport = new FakeTransport();
        $transport->push(new TransportResponse(200, [], json_encode([
            'access_token' => 'token-123',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ], JSON_THROW_ON_ERROR)));
        $transport->push(new TransportResponse(201, [], json_encode([
            'id' => '16fca3f2-b346-4f98-8e52-0895aac61c5b',
            'handle' => 123,
            'status' => 'ACTIVE',
            'display_name' => 'Amelia Rose',
            'email' => null,
            'socials' => [],
            'created_at' => '2026-03-15T09:00:00Z',
            'updated_at' => '2026-03-15T09:00:00Z',
        ], JSON_THROW_ON_ERROR)));

        $sdk = Qredex::init(new QredexConfig(
            auth: new ClientCredentialsAuthentication('client-id', 'client-secret'),
            transport: $transport,
        ));

        $this->expectException(ResponseDecodingError::class);
        $sdk->creators()->create([
            'handle' => 'amelia-rose',
        ]);
    }
}
