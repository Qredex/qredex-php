<?php

declare(strict_types=1);

namespace Qredex\Config;

use Qredex\Error\ConfigurationError;

final readonly class RetryPolicy
{
    public function __construct(
        public int $maxAttempts = 1,
        public int $baseDelayMs = 200,
        public int $maxDelayMs = 2_000,
    ) {
        if ($this->maxAttempts < 1) {
            throw new ConfigurationError('Retry maxAttempts must be greater than or equal to 1.', errorCode: 'sdk_configuration_error');
        }

        if ($this->baseDelayMs < 0 || $this->maxDelayMs < 0) {
            throw new ConfigurationError('Retry delays must be greater than or equal to 0.', errorCode: 'sdk_configuration_error');
        }
    }
}
