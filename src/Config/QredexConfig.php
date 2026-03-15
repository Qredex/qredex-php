<?php

declare(strict_types=1);

namespace Qredex\Config;

use Closure;
use Qredex\Auth\QredexAuthentication;
use Qredex\Cache\TokenCacheInterface;
use Qredex\Error\ConfigurationError;
use Qredex\Http\HttpTransportInterface;
use Psr\Log\LoggerInterface;

final readonly class QredexConfig
{
    public function __construct(
        public QredexAuthentication $auth,
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
    ) {
        if ($this->timeoutMs <= 0) {
            throw new ConfigurationError('Qredex timeoutMs must be greater than 0.', errorCode: 'sdk_configuration_error');
        }

        if ($this->userAgentSuffix !== '' && trim($this->userAgentSuffix) === '') {
            throw new ConfigurationError('Qredex userAgentSuffix cannot be blank whitespace.', errorCode: 'sdk_configuration_error');
        }

        foreach ($this->defaultHeaders as $name => $value) {
            if (!is_string($name) || trim($name) === '' || !is_string($value)) {
                throw new ConfigurationError('Qredex defaultHeaders must be a string map.', errorCode: 'sdk_configuration_error');
            }

            $normalized = strtolower(trim($name));

            if (in_array($normalized, ['authorization', 'content-type', 'user-agent'], true)) {
                throw new ConfigurationError("Qredex defaultHeaders cannot override {$normalized}.", errorCode: 'sdk_configuration_error');
            }
        }
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
}
