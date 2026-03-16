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

namespace Qredex;

use Qredex\Cache\MemoryTokenCache;
use Qredex\Config\QredexConfig;
use Qredex\Http\GuzzleTransport;
use Qredex\Http\HttpTransportInterface;
use Qredex\Internal\EventEmitter;
use Qredex\Internal\HttpClient;
use Qredex\Internal\TokenProvider;
use Qredex\Resource\CreatorsClient;
use Qredex\Resource\IntentsClient;
use Qredex\Resource\LinksClient;
use Qredex\Resource\OrdersClient;
use Qredex\Resource\RefundsClient;

final readonly class Qredex
{
    private QredexAuth $auth;
    private CreatorsClient $creators;
    private LinksClient $links;
    private IntentsClient $intents;
    private OrdersClient $orders;
    private RefundsClient $refunds;

    private function __construct(private QredexConfig $config)
    {
        $eventEmitter = new EventEmitter($this->config->eventListener);
        $transport = $this->resolveTransport($this->config);
        $tokenProvider = new TokenProvider(
            auth: $this->config->auth,
            transport: $transport,
            timeoutMs: $this->config->timeoutMs,
            events: $eventEmitter,
            logger: $this->config->logger,
            retryPolicy: $this->config->authRetry,
            cache: $this->config->tokenCache ?? new MemoryTokenCache(),
        );
        $http = new HttpClient(
            transport: $transport,
            tokenProvider: $tokenProvider,
            timeoutMs: $this->config->timeoutMs,
            defaultHeaders: $this->defaultHeaders($this->config),
            events: $eventEmitter,
            logger: $this->config->logger,
            readRetry: $this->config->readRetry,
            requestIdFactory: $this->config->requestIdFactory,
            requestIdHeader: $this->config->requestIdHeader,
        );

        $this->auth = new QredexAuth($tokenProvider);
        $this->creators = new CreatorsClient($http);
        $this->links = new LinksClient($http);
        $this->intents = new IntentsClient($http);
        $this->orders = new OrdersClient($http);
        $this->refunds = new RefundsClient($http);
    }

    public static function init(QredexConfig $config): self
    {
        return new self($config);
    }

    /**
     * @deprecated Prefer Qredex::init(QredexConfig::fromEnvironment(...)) for a typed bootstrap path.
     *
     * @param array<string, string|null>|null $env
     * @param array<string, mixed> $overrides
     */
    public static function bootstrap(?array $env = null, array $overrides = []): self
    {
        return self::init(QredexConfig::fromEnvironment(
            env: $env,
            scope: $overrides['scope'] ?? null,
            environment: $overrides['environment'] ?? null,
            baseUrl: isset($overrides['baseUrl']) ? (string) $overrides['baseUrl'] : null,
            timeoutMs: self::legacyTimeoutOverride($overrides['timeoutMs'] ?? null),
            transport: $overrides['transport'] ?? null,
            tokenCache: $overrides['tokenCache'] ?? null,
            authRetry: $overrides['authRetry'] ?? null,
            readRetry: $overrides['readRetry'] ?? null,
            logger: $overrides['logger'] ?? null,
            eventListener: is_callable($overrides['eventListener'] ?? null) ? $overrides['eventListener'] : null,
            defaultHeaders: is_array($overrides['defaultHeaders'] ?? null) ? $overrides['defaultHeaders'] : [],
            userAgentSuffix: isset($overrides['userAgentSuffix']) ? (string) $overrides['userAgentSuffix'] : '',
            requestIdFactory: is_callable($overrides['requestIdFactory'] ?? null) ? $overrides['requestIdFactory'] : null,
            requestIdHeader: isset($overrides['requestIdHeader']) ? (string) $overrides['requestIdHeader'] : null,
        ));
    }

    /**
     * @deprecated Qredex manages tokens automatically for canonical SDK usage.
     */
    public function auth(): QredexAuth
    {
        return $this->auth;
    }

    public function creators(): CreatorsClient
    {
        return $this->creators;
    }

    public function links(): LinksClient
    {
        return $this->links;
    }

    public function intents(): IntentsClient
    {
        return $this->intents;
    }

    public function orders(): OrdersClient
    {
        return $this->orders;
    }

    public function refunds(): RefundsClient
    {
        return $this->refunds;
    }

    private function resolveTransport(QredexConfig $config): HttpTransportInterface
    {
        return $config->transport ?? new GuzzleTransport($config->resolvedBaseUrl());
    }

    /**
     * @return array<string, string>
     */
    private function defaultHeaders(QredexConfig $config): array
    {
        $headers = $config->defaultHeaders;
        $headers['user-agent'] = 'qredex-php/' . $this->sdkVersion() . ($config->userAgentSuffix !== '' ? ' ' . trim($config->userAgentSuffix) : '');

        return $headers;
    }

    private function sdkVersion(): string
    {
        try {
            if (class_exists(\Composer\InstalledVersions::class) && method_exists(\Composer\InstalledVersions::class, 'getPrettyVersion')) {
                $version = \Composer\InstalledVersions::getPrettyVersion('qredex/php');

                if (is_string($version) && trim($version) !== '') {
                    return $version;
                }
            }
        } catch (\Throwable) {
            return 'unknown';
        }

        return 'unknown';
    }

    private static function legacyTimeoutOverride(mixed $timeoutMs): ?int
    {
        if (is_int($timeoutMs)) {
            return $timeoutMs;
        }

        if (is_string($timeoutMs) && ctype_digit($timeoutMs)) {
            return (int) $timeoutMs;
        }

        return null;
    }
}
