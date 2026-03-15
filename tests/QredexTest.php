<?php

declare(strict_types=1);

namespace Qredex\Tests;

use PHPUnit\Framework\TestCase;
use Qredex\Auth\ClientCredentialsAuthentication;
use Qredex\Config\QredexConfig;
use Qredex\Config\QredexEnvironment;
use Qredex\Error\AuthenticationError;
use Qredex\Error\ConflictError;
use Qredex\Error\ValidationError;
use Qredex\Http\TransportResponse;
use Qredex\Qredex;

final class QredexTest extends TestCase
{
    public function testBootstrapAndCreateCreatorHappyPath(): void
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

        $sdk = Qredex::init(new QredexConfig(
            auth: new ClientCredentialsAuthentication(
                clientId: 'client-id',
                clientSecret: 'client-secret',
                scope: 'direct:creators:write',
            ),
            environment: QredexEnvironment::PRODUCTION,
            transport: $transport,
        ));

        $creator = $sdk->creators()->create([
            'handle' => 'amelia-rose',
        ]);

        self::assertSame('amelia-rose', $creator->handle);
        self::assertCount(2, $transport->requests);
        self::assertSame('/api/v1/auth/token', $transport->requests[0]->path);
        self::assertSame('/api/v1/integrations/creators', $transport->requests[1]->path);
        self::assertSame('Bearer token-123', $transport->requests[1]->headers['authorization']);
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
                scope: 'direct:orders:read',
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

    public function testValidationFailureStopsBeforeTransport(): void
    {
        $transport = new FakeTransport();
        $sdk = Qredex::init(new QredexConfig(
            auth: new ClientCredentialsAuthentication('client-id', 'client-secret'),
            transport: $transport,
        ));

        $this->expectException(ValidationError::class);

        $sdk->links()->create([
            'store_id' => 'not-a-uuid',
            'creator_id' => 'still-not-a-uuid',
            'link_name' => '',
            'destination_path' => '/ok',
        ]);
    }

    public function testConflictErrorParsingPreservesMetadata(): void
    {
        $transport = new FakeTransport();
        $transport->push(new TransportResponse(200, [], json_encode([
            'access_token' => 'token-123',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'scope' => 'direct:orders:write',
        ], JSON_THROW_ON_ERROR)));
        $transport->push(new TransportResponse(409, [
            'x-request-id' => 'req_123',
            'x-trace-id' => 'trace_456',
        ], json_encode([
            'error_code' => 'REJECTED_CROSS_SOURCE_DUPLICATE',
            'message' => 'Duplicate order under another source.',
        ], JSON_THROW_ON_ERROR)));

        $sdk = Qredex::init(new QredexConfig(
            auth: new ClientCredentialsAuthentication('client-id', 'client-secret', 'direct:orders:write'),
            transport: $transport,
        ));

        try {
            $sdk->orders()->recordPaidOrder([
                'store_id' => '61abc354-dd8d-4a23-be02-ece77b1b4da6',
                'external_order_id' => 'order-100045',
                'currency' => 'USD',
            ]);
            self::fail('Expected ConflictError to be thrown.');
        } catch (ConflictError $error) {
            self::assertSame(409, $error->status);
            self::assertSame('REJECTED_CROSS_SOURCE_DUPLICATE', $error->errorCode);
            self::assertSame('req_123', $error->requestId);
            self::assertSame('trace_456', $error->traceId);
        }
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
        $sdk->auth()->issueToken();
    }

    public function testLatestUnlockedValidatesHoursRange(): void
    {
        $transport = new FakeTransport();
        $sdk = Qredex::init(new QredexConfig(
            auth: new ClientCredentialsAuthentication('client-id', 'client-secret'),
            transport: $transport,
        ));

        $this->expectException(ValidationError::class);
        $sdk->intents()->latestUnlocked(0);
    }
}
