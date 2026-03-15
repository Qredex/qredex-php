<?php

declare(strict_types=1);

namespace Qredex;

use Closure;
use Qredex\Auth\ClientCredentialsAuthentication;
use Qredex\Cache\MemoryTokenCache;
use Qredex\Config\QredexConfig;
use Qredex\Config\QredexEnvironment;
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
     * @param array<string, string|null>|null $env
     * @param array<string, mixed> $overrides
     */
    public static function bootstrap(?array $env = null, array $overrides = []): self
    {
        $env = array_merge($_SERVER, $_ENV, $env ?? []);
        $clientId = trim((string) ($env['QREDEX_CLIENT_ID'] ?? ''));
        $clientSecret = trim((string) ($env['QREDEX_CLIENT_SECRET'] ?? ''));
        $scope = $overrides['scope'] ?? ($env['QREDEX_SCOPE'] ?? null);
        $environmentValue = $overrides['environment'] ?? ($env['QREDEX_ENVIRONMENT'] ?? 'production');

        if ($clientId === '') {
            throw new ConfigurationError('Qredex bootstrap requires QREDEX_CLIENT_ID.', errorCode: 'sdk_configuration_error');
        }

        if ($clientSecret === '') {
            throw new ConfigurationError('Qredex bootstrap requires QREDEX_CLIENT_SECRET.', errorCode: 'sdk_configuration_error');
        }

        $eventListener = $overrides['eventListener'] ?? null;
        $closure = $eventListener instanceof Closure
            ? $eventListener
            : (is_callable($eventListener) ? Closure::fromCallable($eventListener) : null);

        $config = new QredexConfig(
            auth: new ClientCredentialsAuthentication(
                clientId: $clientId,
                clientSecret: $clientSecret,
                scope: $scope,
            ),
            environment: $environmentValue instanceof QredexEnvironment
                ? $environmentValue
                : QredexEnvironment::fromString((string) $environmentValue),
            baseUrl: isset($overrides['baseUrl']) ? (string) $overrides['baseUrl'] : ((isset($env['QREDEX_BASE_URL']) && $env['QREDEX_BASE_URL'] !== null) ? (string) $env['QREDEX_BASE_URL'] : null),
            timeoutMs: (int) ($overrides['timeoutMs'] ?? ($env['QREDEX_TIMEOUT_MS'] ?? 10_000)),
            transport: $overrides['transport'] ?? null,
            tokenCache: $overrides['tokenCache'] ?? null,
            authRetry: $overrides['authRetry'] ?? null,
            readRetry: $overrides['readRetry'] ?? null,
            logger: $overrides['logger'] ?? null,
            eventListener: $closure,
            defaultHeaders: is_array($overrides['defaultHeaders'] ?? null) ? $overrides['defaultHeaders'] : [],
            userAgentSuffix: isset($overrides['userAgentSuffix']) ? (string) $overrides['userAgentSuffix'] : '',
        );

        return self::init($config);
    }

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
        $headers['user-agent'] = 'qredex-php/0.1.0' . ($config->userAgentSuffix !== '' ? ' ' . trim($config->userAgentSuffix) : '');

        return $headers;
    }
}
