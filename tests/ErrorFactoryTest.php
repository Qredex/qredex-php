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
use Qredex\Error\ApiError;
use Qredex\Error\ApiValidationError;
use Qredex\Error\AuthenticationError;
use Qredex\Error\AuthorizationError;
use Qredex\Error\ConflictError;
use Qredex\Error\NotFoundError;
use Qredex\Error\RateLimitError;
use Qredex\Http\TransportResponse;
use Qredex\Internal\ErrorFactory;

final class ErrorFactoryTest extends TestCase
{
    /** @param array<string, string> $headers */
    private function makeResponse(int $status, string $body, array $headers = []): TransportResponse
    {
        return new TransportResponse($status, $headers, $body);
    }

    private function jsonBody(string $errorCode, string $message): string
    {
        return json_encode(['error_code' => $errorCode, 'message' => $message], JSON_THROW_ON_ERROR);
    }

    public function testStatus400ReturnsApiValidationError(): void
    {
        $error = ErrorFactory::fromResponse(
            $this->makeResponse(400, $this->jsonBody('bad_request', 'Bad request.')),
        );
        self::assertInstanceOf(ApiValidationError::class, $error);
        self::assertSame(400, $error->status);
    }

    public function testStatus401ReturnsAuthenticationError(): void
    {
        $error = ErrorFactory::fromResponse(
            $this->makeResponse(401, $this->jsonBody('invalid_client', 'Client authentication failed.')),
        );
        self::assertInstanceOf(AuthenticationError::class, $error);
        self::assertSame(401, $error->status);
    }

    public function testStatus403ReturnsAuthorizationError(): void
    {
        $error = ErrorFactory::fromResponse(
            $this->makeResponse(403, $this->jsonBody('insufficient_scope', 'Forbidden.')),
        );
        self::assertInstanceOf(AuthorizationError::class, $error);
        self::assertSame(403, $error->status);
    }

    public function testStatus404ReturnsNotFoundError(): void
    {
        $error = ErrorFactory::fromResponse(
            $this->makeResponse(404, $this->jsonBody('not_found', 'Resource not found.')),
        );
        self::assertInstanceOf(NotFoundError::class, $error);
        self::assertSame(404, $error->status);
        self::assertSame('not_found', $error->errorCode);
    }

    public function testStatus409ReturnsConflictError(): void
    {
        $error = ErrorFactory::fromResponse(
            $this->makeResponse(409, $this->jsonBody('conflict', 'Duplicate resource.')),
        );
        self::assertInstanceOf(ConflictError::class, $error);
        self::assertSame(409, $error->status);
    }

    public function testStatus422ReturnsApiValidationError(): void
    {
        $error = ErrorFactory::fromResponse(
            $this->makeResponse(422, $this->jsonBody('validation_error', 'Invalid currency.')),
        );
        self::assertInstanceOf(ApiValidationError::class, $error);
        self::assertSame(422, $error->status);
    }

    public function testStatus429ReturnsRateLimitError(): void
    {
        $error = ErrorFactory::fromResponse(
            $this->makeResponse(429, $this->jsonBody('rate_limited', 'Slow down.'), ['retry-after' => '5']),
        );
        self::assertInstanceOf(RateLimitError::class, $error);
        self::assertSame(429, $error->status);
        self::assertSame(5, $error->retryAfterSeconds);
    }

    public function testStatus500ReturnsApiError(): void
    {
        $error = ErrorFactory::fromResponse(
            $this->makeResponse(500, $this->jsonBody('internal_error', 'Internal server error.')),
        );
        self::assertInstanceOf(ApiError::class, $error);
        self::assertSame(500, $error->status);
    }

    public function testErrorPreservesMetadata(): void
    {
        $error = ErrorFactory::fromResponse(
            $this->makeResponse(
                422,
                $this->jsonBody('validation_error', 'Order currency is invalid.'),
                [
                    'x-request-id' => 'req_abc123',
                    'x-trace-id' => 'trace_xyz789',
                    'retry-after' => '10',
                ],
            ),
        );

        self::assertSame('req_abc123', $error->requestId);
        self::assertSame('trace_xyz789', $error->traceId);
        self::assertSame('validation_error', $error->errorCode);
        self::assertSame(10, $error->retryAfterSeconds);
        self::assertSame('Order currency is invalid.', $error->getMessage());
    }

    public function testMalformedJsonBodyFallsBackToDefaultMessage(): void
    {
        $error = ErrorFactory::fromResponse(
            $this->makeResponse(500, 'this is not json'),
        );
        self::assertInstanceOf(ApiError::class, $error);
        self::assertStringContainsString('status 500', $error->getMessage());
        self::assertNull($error->errorCode);
    }
}
