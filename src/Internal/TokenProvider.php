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
use Qredex\Auth\AccessTokenAuthentication;
use Qredex\Auth\ClientCredentialsAuthentication;
use Qredex\Auth\QredexAuthentication;
use Qredex\Auth\QredexScope;
use Qredex\Cache\CachedToken;
use Qredex\Cache\MemoryTokenCache;
use Qredex\Cache\TokenCacheInterface;
use Qredex\Config\RetryPolicy;
use Qredex\Error\ConfigurationError;
use Qredex\Error\NetworkError;
use Qredex\Error\QredexError;
use Qredex\Http\HttpTransportInterface;
use Qredex\Http\TransportRequest;
use Qredex\Model\OAuthToken;

final class TokenProvider
{
    private readonly TokenCacheInterface $cache;

    public function __construct(
        private readonly QredexAuthentication $auth,
        private readonly HttpTransportInterface $transport,
        private readonly int $timeoutMs,
        private readonly EventEmitter $events,
        private readonly ?LoggerInterface $logger = null,
        private readonly ?RetryPolicy $retryPolicy = null,
        ?TokenCacheInterface $cache = null,
    ) {
        $this->cache = $cache ?? new MemoryTokenCache();
    }

    public function clear(): void
    {
        $this->cache->clear();
    }

    /**
     * @param string|QredexScope|list<string|QredexScope>|null $scope
     */
    public function issueToken(string|QredexScope|array|null $scope = null): OAuthToken
    {
        if (!$this->auth instanceof ClientCredentialsAuthentication) {
            throw new ConfigurationError('Explicit token issuance requires client credentials auth.', errorCode: 'sdk_configuration_error');
        }

        $requestedScope = $this->normalizeScope($scope) ?? $this->auth->normalizedScope();
        $shouldCache = $requestedScope === $this->auth->normalizedScope();

        return $this->fetchToken($requestedScope, $shouldCache);
    }

    public function authorizationHeader(): string
    {
        if ($this->auth instanceof AccessTokenAuthentication) {
            return 'Bearer ' . $this->resolveAccessToken($this->auth->accessToken);
        }

        $cached = $this->cache->get();
        $refreshThreshold = time() + $this->auth->refreshWindowSeconds;

        if ($cached !== null && $cached->expiresAtUnix > $refreshThreshold) {
            $this->events->emit('auth_cache_hit', [
                'expires_at' => gmdate(DATE_ATOM, $cached->expiresAtUnix),
                'scope' => $cached->scope,
            ]);

            return "{$cached->tokenType} {$cached->accessToken}";
        }

        $this->events->emit('auth_cache_miss', [
            'scope' => $this->auth->normalizedScope(),
        ]);

        $token = $this->fetchToken($this->auth->normalizedScope(), true);

        return "{$token->tokenType} {$token->accessToken}";
    }

    private function fetchToken(?string $scope, bool $shouldCache): OAuthToken
    {
        if (!$this->auth instanceof ClientCredentialsAuthentication) {
            throw new ConfigurationError('Client credentials auth is required to fetch a Qredex token.', errorCode: 'sdk_configuration_error');
        }

        $retryPolicy = $this->retryPolicy ?? new RetryPolicy();
        $lastError = null;

        for ($attempt = 1; $attempt <= $retryPolicy->maxAttempts; $attempt++) {
            try {
                $body = ['grant_type' => 'client_credentials'];

                if ($scope !== null) {
                    $body['scope'] = $scope;
                }

                $response = $this->transport->send(new TransportRequest(
                    method: 'POST',
                    path: '/api/v1/auth/token',
                    headers: [
                        'accept' => 'application/json',
                        'authorization' => 'Basic ' . base64_encode($this->auth->clientId . ':' . $this->auth->clientSecret),
                    ],
                    body: $body,
                    bodyType: TransportRequest::BODY_FORM,
                    timeoutMs: $this->timeoutMs,
                ));

                if ($response->status < 200 || $response->status >= 300) {
                    throw ErrorFactory::fromResponse($response);
                }

                $decoded = json_decode($response->body, true);

                if (!is_array($decoded)) {
                    throw new NetworkError('Qredex auth returned a non-JSON response.');
                }

                $token = OAuthToken::fromArray($decoded);

                if ($shouldCache) {
                    $this->cache->set(new CachedToken(
                        accessToken: $token->accessToken,
                        tokenType: $token->tokenType,
                        expiresAtUnix: time() + $token->expiresIn,
                        scope: $token->scope,
                    ));
                }

                $this->events->emit('auth_token_issued', [
                    'expires_at' => gmdate(DATE_ATOM, time() + $token->expiresIn),
                    'scope' => $token->scope,
                    'token_type' => $token->tokenType,
                ]);
                $this->logger?->info('qredex.auth.token_issued', [
                    'scope' => $token->scope,
                    'token_type' => $token->tokenType,
                    'expires_in' => $token->expiresIn,
                ]);

                return $token;
            } catch (QredexError $error) {
                $lastError = $error;

                if ($attempt >= $retryPolicy->maxAttempts || !$this->shouldRetry($error)) {
                    throw $error;
                }

                $delay = Retry::delayMs($retryPolicy, $attempt, $error->retryAfterSeconds);
                $this->events->emit('retry_scheduled', [
                    'attempt' => $attempt,
                    'delay_ms' => $delay,
                    'max_attempts' => $retryPolicy->maxAttempts,
                    'path' => '/api/v1/auth/token',
                    'retry_after_seconds' => $error->retryAfterSeconds,
                    'source' => 'auth',
                ]);
                Retry::sleep($delay);
            }
        }

        throw $lastError ?? new NetworkError('Qredex token issuance failed unexpectedly.');
    }

    /**
     * @param string|QredexScope|list<string|QredexScope>|null $scope
     */
    private function normalizeScope(string|QredexScope|array|null $scope): ?string
    {
        if ($scope === null) {
            return null;
        }

        if ($scope instanceof QredexScope) {
            return $scope->value;
        }

        if (is_string($scope)) {
            $normalized = preg_replace('/[\s,]+/', ' ', trim($scope));

            return $normalized === '' ? null : $normalized;
        }

        $parts = array_values(array_filter(array_map(
            static fn (mixed $value): string => trim($value instanceof QredexScope ? $value->value : (string) $value),
            $scope,
        ), static fn (string $value): bool => $value !== ''));

        return $parts === [] ? null : implode(' ', $parts);
    }

    private function resolveAccessToken(string|Closure $accessToken): string
    {
        $resolved = $accessToken instanceof Closure ? $accessToken() : $accessToken;

        if (!is_string($resolved) || trim($resolved) === '') {
            throw new ConfigurationError('Qredex access token resolved to an empty value.', errorCode: 'sdk_configuration_error');
        }

        return $resolved;
    }

    private function shouldRetry(QredexError $error): bool
    {
        if ($error instanceof NetworkError) {
            return true;
        }

        return $error->status !== null && Retry::shouldRetryStatus($error->status);
    }
}
