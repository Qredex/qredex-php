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
