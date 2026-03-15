<?php

declare(strict_types=1);

namespace Qredex\Config;

use Qredex\Error\ConfigurationError;

enum QredexEnvironment: string
{
    case PRODUCTION = 'production';
    case STAGING = 'staging';
    case DEVELOPMENT = 'development';

    public function baseUrl(): string
    {
        return match ($this) {
            self::PRODUCTION => 'https://api.qredex.com',
            self::STAGING => 'https://staging-api.qredex.com',
            self::DEVELOPMENT => 'http://localhost:8080',
        };
    }

    public static function fromString(string $value): self
    {
        return match (strtolower(trim($value))) {
            'production' => self::PRODUCTION,
            'staging' => self::STAGING,
            'development' => self::DEVELOPMENT,
            default => throw new ConfigurationError(
                "Qredex environment must be 'production', 'staging', or 'development'.",
                errorCode: 'sdk_configuration_error',
            ),
        };
    }
}
