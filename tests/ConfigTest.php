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
use Qredex\Config\QredexEnvironment;
use Qredex\Config\RetryPolicy;
use Qredex\Error\ConfigurationError;

final class ConfigTest extends TestCase
{
    private function auth(): ClientCredentialsAuthentication
    {
        return new ClientCredentialsAuthentication('client-id', 'client-secret');
    }

    public function testTimeoutMustBePositiveZero(): void
    {
        $this->expectException(ConfigurationError::class);
        new QredexConfig(auth: $this->auth(), timeoutMs: 0);
    }

    public function testTimeoutMustBePositiveNegative(): void
    {
        $this->expectException(ConfigurationError::class);
        new QredexConfig(auth: $this->auth(), timeoutMs: -1);
    }

    public function testUserAgentSuffixCannotBeBlankWhitespace(): void
    {
        $this->expectException(ConfigurationError::class);
        new QredexConfig(auth: $this->auth(), userAgentSuffix: ' ');
    }

    public function testUserAgentSuffixAcceptsEmptyString(): void
    {
        $config = new QredexConfig(auth: $this->auth(), userAgentSuffix: '');
        self::assertSame('', $config->userAgentSuffix);
    }

    public function testUserAgentSuffixAcceptsCustomValue(): void
    {
        $config = new QredexConfig(auth: $this->auth(), userAgentSuffix: 'custom');
        self::assertSame('custom', $config->userAgentSuffix);
    }

    public function testRequestIdHeaderMustBeLowercaseToken(): void
    {
        $this->expectException(ConfigurationError::class);
        new QredexConfig(auth: $this->auth(), requestIdHeader: 'Content-Type');
    }

    public function testRequestIdHeaderAcceptsValid(): void
    {
        $config = new QredexConfig(auth: $this->auth(), requestIdHeader: 'x-req-id');
        self::assertSame('x-req-id', $config->requestIdHeader);
    }

    public function testDefaultHeadersCannotOverrideReserved(): void
    {
        $this->expectException(ConfigurationError::class);
        new QredexConfig(auth: $this->auth(), defaultHeaders: ['authorization' => 'Bearer bad']);
    }

    public function testRetryPolicyMaxAttemptsMustBePositive(): void
    {
        $this->expectException(ConfigurationError::class);
        new RetryPolicy(maxAttempts: 0);
    }

    public function testRetryPolicyMaxDelayMustBeGreaterThanBase(): void
    {
        $this->expectException(ConfigurationError::class);
        new RetryPolicy(maxAttempts: 2, baseDelayMs: 500, maxDelayMs: 100);
    }

    public function testEnvironmentFromStringProduction(): void
    {
        self::assertSame(QredexEnvironment::PRODUCTION, QredexEnvironment::fromString('production'));
    }

    public function testEnvironmentFromStringStaging(): void
    {
        self::assertSame(QredexEnvironment::STAGING, QredexEnvironment::fromString('staging'));
    }

    public function testEnvironmentFromStringDevelopment(): void
    {
        self::assertSame(QredexEnvironment::DEVELOPMENT, QredexEnvironment::fromString('development'));
    }

    public function testEnvironmentFromStringInvalid(): void
    {
        $this->expectException(ConfigurationError::class);
        QredexEnvironment::fromString('sandbox');
    }

    public function testEnvironmentBaseUrls(): void
    {
        self::assertSame('https://api.qredex.com', QredexEnvironment::PRODUCTION->baseUrl());
        self::assertSame('https://staging-api.qredex.com', QredexEnvironment::STAGING->baseUrl());
        self::assertSame('http://localhost:8080', QredexEnvironment::DEVELOPMENT->baseUrl());
    }

    public function testClientCredentialsSingleScope(): void
    {
        $auth = new ClientCredentialsAuthentication('id', 'secret', 'direct:api');
        self::assertSame('direct:api', $auth->normalizedScope());
    }

    public function testClientCredentialsScopeAsEnum(): void
    {
        $auth = new ClientCredentialsAuthentication('id', 'secret', QredexScope::CREATORS_WRITE);
        self::assertSame('direct:creators:write', $auth->normalizedScope());
    }

    public function testClientCredentialsScopeAsArray(): void
    {
        $auth = new ClientCredentialsAuthentication('id', 'secret', [
            QredexScope::CREATORS_READ,
            'direct:orders:write',
        ]);
        self::assertSame('direct:creators:read direct:orders:write', $auth->normalizedScope());
    }

    public function testClientCredentialsScopeCommaSeparatedString(): void
    {
        $auth = new ClientCredentialsAuthentication('id', 'secret', 'direct:creators:read,direct:orders:write');
        self::assertSame('direct:creators:read direct:orders:write', $auth->normalizedScope());
    }

    public function testClientCredentialsRequiresNonEmptyClientId(): void
    {
        $this->expectException(ConfigurationError::class);
        new ClientCredentialsAuthentication('', 'secret');
    }

    public function testClientCredentialsRequiresNonEmptyClientSecret(): void
    {
        $this->expectException(ConfigurationError::class);
        new ClientCredentialsAuthentication('id', '');
    }

    public function testFromEnvironmentRequiresClientId(): void
    {
        $this->expectException(ConfigurationError::class);
        QredexConfig::fromEnvironment(env: ['QREDEX_CLIENT_SECRET' => 'secret']);
    }

    public function testFromEnvironmentRequiresClientSecret(): void
    {
        $this->expectException(ConfigurationError::class);
        QredexConfig::fromEnvironment(env: ['QREDEX_CLIENT_ID' => 'id']);
    }
}
