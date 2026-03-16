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
use Qredex\Error\ConfigurationError;
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
    public const SDK_VERSION = '0.1.0';

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

        $this->creators = new CreatorsClient($http);
        $this->links = new LinksClient($http);
        $this->intents = new IntentsClient($http);
        $this->orders = new OrdersClient($http);
        $this->refunds = new RefundsClient($http);
    }

    /**
     * @throws ConfigurationError
     */
    public static function init(QredexConfig $config): self
    {
        return new self($config);
    }

    /**
     * @param array<string, string|null>|null $env
     *
     * @throws ConfigurationError
     */
    public static function bootstrap(?array $env = null): self
    {
        return self::init(QredexConfig::fromEnvironment(env: $env));
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
        return self::SDK_VERSION;
    }
}
