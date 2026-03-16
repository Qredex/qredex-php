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
use Qredex\Error\ApiValidationError;
use Qredex\Error\AuthenticationError;
use Qredex\Error\RequestValidationError;
use Qredex\Http\TransportResponse;
use Qredex\Qredex;
use Qredex\Request\CreateCreatorRequest;

final class QredexTest extends TestCase
{
    public function testLegacyBootstrapAndArrayPayloadStillWork(): void
    {
        $transport = new FakeTransport();
        $transport->push(new TransportResponse(200, [], json_encode([
            'access_token' => 'token-123',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'scope' => 'direct:creators:write',
        ], JSON_THROW_ON_ERROR)));
        $transport->push(new TransportResponse(201, [], json_encode([
            'id' => '16fca3f2-b346-4f98-8e52-0895aac61c5b',
            'handle' => 'amelia-rose',
            'status' => 'ACTIVE',
            'display_name' => 'Amelia Rose',
            'email' => null,
            'socials' => [],
            'created_at' => '2026-03-15T09:00:00Z',
            'updated_at' => '2026-03-15T09:00:00Z',
        ], JSON_THROW_ON_ERROR)));

        $sdk = Qredex::bootstrap([
            'QREDEX_CLIENT_ID' => 'client-id',
            'QREDEX_CLIENT_SECRET' => 'client-secret',
        ], [
            'scope' => QredexScope::CREATORS_WRITE,
            'transport' => $transport,
        ]);

        $creator = $sdk->creators()->create([
            'handle' => 'amelia-rose',
        ]);

        self::assertSame('amelia-rose', $creator->handle);
        self::assertCount(2, $transport->requests);
        self::assertSame('/api/v1/auth/token', $transport->requests[0]->path);
        self::assertSame('/api/v1/integrations/creators', $transport->requests[1]->path);
    }

    public function testConfigFromEnvironmentSupportsTypedRequestObjects(): void
    {
        $transport = new FakeTransport();
        $transport->push(new TransportResponse(200, [], json_encode([
            'access_token' => 'token-456',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'scope' => 'direct:creators:write',
        ], JSON_THROW_ON_ERROR)));
        $transport->push(new TransportResponse(201, [], json_encode([
            'id' => '16fca3f2-b346-4f98-8e52-0895aac61c5b',
            'handle' => 'amelia-rose',
            'status' => 'ACTIVE',
            'display_name' => 'Amelia Rose',
            'email' => 'ops@example.com',
            'socials' => [],
            'created_at' => '2026-03-15T09:00:00Z',
            'updated_at' => '2026-03-15T09:00:00Z',
        ], JSON_THROW_ON_ERROR)));

        $sdk = Qredex::init(QredexConfig::fromEnvironment(
            env: [
                'QREDEX_CLIENT_ID' => 'client-id',
                'QREDEX_CLIENT_SECRET' => 'client-secret',
                'QREDEX_TIMEOUT_MS' => '15000',
            ],
            scope: QredexScope::CREATORS_WRITE,
            transport: $transport,
        ));

        $creator = $sdk->creators()->create(new CreateCreatorRequest(
            handle: 'amelia-rose',
            displayName: 'Amelia Rose',
            email: 'ops@example.com',
        ));

        self::assertSame('Amelia Rose', $creator->displayName);
        self::assertSame([
            'handle' => 'amelia-rose',
            'display_name' => 'Amelia Rose',
            'email' => 'ops@example.com',
        ], $transport->requests[1]->body);
    }

    public function testTokenIsReusedUntilExpiry(): void
    {
        $transport = new FakeTransport();
        $transport->push(new TransportResponse(200, [], json_encode([
            'access_token' => 'cached-token',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ], JSON_THROW_ON_ERROR)));
        $transport->push(new TransportResponse(200, [], json_encode([
            'items' => [],
            'page' => 0,
            'size' => 25,
            'total_elements' => 0,
            'total_pages' => 0,
        ], JSON_THROW_ON_ERROR)));
        $transport->push(new TransportResponse(200, [], json_encode([
            'items' => [],
            'page' => 0,
            'size' => 25,
            'total_elements' => 0,
            'total_pages' => 0,
        ], JSON_THROW_ON_ERROR)));

        $sdk = Qredex::init(new QredexConfig(
            auth: new ClientCredentialsAuthentication(
                clientId: 'client-id',
                clientSecret: 'client-secret',
                scope: QredexScope::ORDERS_READ,
            ),
            transport: $transport,
        ));

        $sdk->orders()->list();
        $sdk->orders()->list();

        self::assertCount(3, $transport->requests);
        self::assertSame('/api/v1/auth/token', $transport->requests[0]->path);
        self::assertSame('/api/v1/integrations/orders', $transport->requests[1]->path);
        self::assertSame('/api/v1/integrations/orders', $transport->requests[2]->path);
    }

    public function testRequestValidationFailureStopsBeforeTransport(): void
    {
        $transport = new FakeTransport();
        $sdk = Qredex::init(new QredexConfig(
            auth: new ClientCredentialsAuthentication('client-id', 'client-secret'),
            transport: $transport,
        ));

        $this->expectException(RequestValidationError::class);

        $sdk->links()->create([
            'store_id' => 'not-a-uuid',
            'creator_id' => 'still-not-a-uuid',
            'link_name' => '',
            'destination_path' => '/ok',
        ]);
    }

    public function testApiValidationErrorParsingIsTyped(): void
    {
        $transport = new FakeTransport();
        $transport->push(new TransportResponse(200, [], json_encode([
            'access_token' => 'token-123',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'scope' => 'direct:orders:write',
        ], JSON_THROW_ON_ERROR)));
        $transport->push(new TransportResponse(422, [
            'x-request-id' => 'req_123',
        ], json_encode([
            'error_code' => 'validation_error',
            'message' => 'Order currency is invalid.',
        ], JSON_THROW_ON_ERROR)));

        $sdk = Qredex::init(new QredexConfig(
            auth: new ClientCredentialsAuthentication('client-id', 'client-secret', QredexScope::ORDERS_WRITE),
            transport: $transport,
        ));

        $this->expectException(ApiValidationError::class);
        $sdk->orders()->recordPaidOrder([
            'store_id' => '61abc354-dd8d-4a23-be02-ece77b1b4da6',
            'external_order_id' => 'order-100045',
            'currency' => 'USD',
        ]);
    }

    public function testAuthenticationErrorParsingIsTyped(): void
    {
        $transport = new FakeTransport();
        $transport->push(new TransportResponse(401, [], json_encode([
            'error_code' => 'invalid_client',
            'message' => 'Client authentication failed.',
        ], JSON_THROW_ON_ERROR)));

        $sdk = Qredex::init(new QredexConfig(
            auth: new ClientCredentialsAuthentication('client-id', 'client-secret'),
            transport: $transport,
        ));

        $this->expectException(AuthenticationError::class);
        $sdk->orders()->list();
    }
}
