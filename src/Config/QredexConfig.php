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

namespace Qredex\Config;

use Closure;
use Psr\Log\LoggerInterface;
use Qredex\Auth\ClientCredentialsAuthentication;
use Qredex\Auth\QredexScope;
use Qredex\Cache\TokenCacheInterface;
use Qredex\Error\ConfigurationError;
use Qredex\Http\HttpTransportInterface;

final readonly class QredexConfig
{
    public function __construct(
        public ClientCredentialsAuthentication $auth,
        public QredexEnvironment $environment = QredexEnvironment::PRODUCTION,
        public ?string $baseUrl = null,
        public int $timeoutMs = 10_000,
        public ?HttpTransportInterface $transport = null,
        public ?TokenCacheInterface $tokenCache = null,
        public ?RetryPolicy $authRetry = null,
        public ?RetryPolicy $readRetry = null,
        public ?LoggerInterface $logger = null,
        public ?Closure $eventListener = null,
        public array $defaultHeaders = [],
        public string $userAgentSuffix = '',
        public ?Closure $requestIdFactory = null,
        public ?string $requestIdHeader = null,
    ) {
        if ($this->timeoutMs <= 0) {
            throw new ConfigurationError('Qredex timeoutMs must be greater than 0.', errorCode: 'sdk_configuration_error');
        }

        if ($this->userAgentSuffix !== '' && trim($this->userAgentSuffix) === '') {
            throw new ConfigurationError('Qredex userAgentSuffix cannot be blank whitespace.', errorCode: 'sdk_configuration_error');
        }

        if ($this->requestIdHeader !== null) {
            $normalizedHeader = strtolower(trim($this->requestIdHeader));

            if ($normalizedHeader === '' || !preg_match('/^[a-z0-9-]+$/', $normalizedHeader)) {
                throw new ConfigurationError('Qredex requestIdHeader must be a lowercase HTTP header token.', errorCode: 'sdk_configuration_error');
            }

            if (in_array($normalizedHeader, ['authorization', 'content-type', 'user-agent'], true)) {
                throw new ConfigurationError("Qredex requestIdHeader cannot override {$normalizedHeader}.", errorCode: 'sdk_configuration_error');
            }
        }

        foreach ($this->defaultHeaders as $name => $value) {
            if (!is_string($name) || trim($name) === '' || !is_string($value)) {
                throw new ConfigurationError('Qredex defaultHeaders must be a string map.', errorCode: 'sdk_configuration_error');
            }

            $normalized = strtolower(trim($name));

            if (in_array($normalized, ['authorization', 'content-type', 'user-agent'], true)) {
                throw new ConfigurationError("Qredex defaultHeaders cannot override {$normalized}.", errorCode: 'sdk_configuration_error');
            }

            if ($this->requestIdHeader !== null && $normalized === strtolower($this->requestIdHeader)) {
                throw new ConfigurationError('Qredex defaultHeaders cannot override requestIdHeader.', errorCode: 'sdk_configuration_error');
            }
        }
    }

    /**
     * @param array<string, string|null>|null $env
     * @param array<string, string> $defaultHeaders
     * @param string|QredexScope|list<string|QredexScope>|null $scope
     */
    public static function fromEnvironment(
        ?array $env = null,
        string|QredexScope|array|null $scope = null,
        QredexEnvironment|string|null $environment = null,
        ?string $baseUrl = null,
        ?int $timeoutMs = null,
        ?HttpTransportInterface $transport = null,
        ?TokenCacheInterface $tokenCache = null,
        ?RetryPolicy $authRetry = null,
        ?RetryPolicy $readRetry = null,
        ?LoggerInterface $logger = null,
        callable|null $eventListener = null,
        array $defaultHeaders = [],
        string $userAgentSuffix = '',
        callable|null $requestIdFactory = null,
        ?string $requestIdHeader = null,
    ): self {
        $env = array_merge($_SERVER, $_ENV, $env ?? []);
        $clientId = trim((string) ($env['QREDEX_CLIENT_ID'] ?? ''));
        $clientSecret = trim((string) ($env['QREDEX_CLIENT_SECRET'] ?? ''));
        $scope = $scope ?? ($env['QREDEX_SCOPE'] ?? null);
        $environment = $environment ?? ($env['QREDEX_ENVIRONMENT'] ?? 'production');
        $baseUrl = $baseUrl ?? ((isset($env['QREDEX_BASE_URL']) && $env['QREDEX_BASE_URL'] !== '') ? (string) $env['QREDEX_BASE_URL'] : null);
        $timeoutMs = $timeoutMs ?? self::parseTimeoutMs($env['QREDEX_TIMEOUT_MS'] ?? null);

        if ($clientId === '') {
            throw new ConfigurationError('Qredex bootstrap requires QREDEX_CLIENT_ID.', errorCode: 'sdk_configuration_error');
        }

        if ($clientSecret === '') {
            throw new ConfigurationError('Qredex bootstrap requires QREDEX_CLIENT_SECRET.', errorCode: 'sdk_configuration_error');
        }

        return new self(
            auth: new ClientCredentialsAuthentication(
                clientId: $clientId,
                clientSecret: $clientSecret,
                scope: $scope,
            ),
            environment: $environment instanceof QredexEnvironment
                ? $environment
                : QredexEnvironment::fromString((string) $environment),
            baseUrl: $baseUrl,
            timeoutMs: $timeoutMs,
            transport: $transport,
            tokenCache: $tokenCache,
            authRetry: $authRetry,
            readRetry: $readRetry,
            logger: $logger,
            eventListener: self::normalizeCallable($eventListener),
            defaultHeaders: $defaultHeaders,
            userAgentSuffix: $userAgentSuffix,
            requestIdFactory: self::normalizeCallable($requestIdFactory),
            requestIdHeader: $requestIdHeader === null ? null : strtolower(trim($requestIdHeader)),
        );
    }

    public function resolvedBaseUrl(): string
    {
        $baseUrl = $this->baseUrl ?? $this->environment->baseUrl();
        $parsed = parse_url($baseUrl);

        if (!is_array($parsed) || !isset($parsed['scheme']) || !in_array($parsed['scheme'], ['http', 'https'], true)) {
            throw new ConfigurationError('Qredex baseUrl must be a valid http or https URL.', errorCode: 'sdk_configuration_error');
        }

        return rtrim($baseUrl, '/');
    }

    private static function parseTimeoutMs(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 10_000;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && ctype_digit($value)) {
            return (int) $value;
        }

        throw new ConfigurationError('QREDEX_TIMEOUT_MS must be a positive integer.', errorCode: 'sdk_configuration_error');
    }

    private static function normalizeCallable(callable|null $callable): ?Closure
    {
        if ($callable === null) {
            return null;
        }

        return $callable instanceof Closure ? $callable : Closure::fromCallable($callable);
    }
}
