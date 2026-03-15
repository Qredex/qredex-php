<?php

declare(strict_types=1);

namespace Qredex\Auth;

use Qredex\Error\ConfigurationError;

final readonly class ClientCredentialsAuthentication implements QredexAuthentication
{
    public function __construct(
        public string $clientId,
        public string $clientSecret,
        public string|array|null $scope = null,
        public int $refreshWindowSeconds = 30,
    ) {
        if (trim($this->clientId) === '') {
            throw new ConfigurationError('Qredex clientId must be a non-empty string.', errorCode: 'sdk_configuration_error');
        }

        if (trim($this->clientSecret) === '') {
            throw new ConfigurationError('Qredex clientSecret must be a non-empty string.', errorCode: 'sdk_configuration_error');
        }

        if ($this->refreshWindowSeconds < 0) {
            throw new ConfigurationError('Qredex refreshWindowSeconds must be greater than or equal to 0.', errorCode: 'sdk_configuration_error');
        }
    }

    public function normalizedScope(): ?string
    {
        if ($this->scope === null) {
            return null;
        }

        if (is_string($this->scope)) {
            $scope = preg_replace('/[\s,]+/', ' ', trim($this->scope));

            return $scope === '' ? null : $scope;
        }

        $parts = array_values(array_filter(array_map(static fn (mixed $value): string => trim((string) $value), $this->scope), static fn (string $value): bool => $value !== ''));

        return $parts === [] ? null : implode(' ', $parts);
    }
}
