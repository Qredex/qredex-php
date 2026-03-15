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

final readonly class RetryPolicy
{
    public function __construct(
        public int $maxAttempts = 1,
        public int $baseDelayMs = 200,
        public int $maxDelayMs = 2_000,
        public bool $useJitter = true,
    ) {
        if ($this->maxAttempts < 1) {
            throw new ConfigurationError('Retry maxAttempts must be greater than or equal to 1.', errorCode: 'sdk_configuration_error');
        }

        if ($this->baseDelayMs < 0 || $this->maxDelayMs < 0) {
            throw new ConfigurationError('Retry delays must be greater than or equal to 0.', errorCode: 'sdk_configuration_error');
        }

        if ($this->maxDelayMs > 0 && $this->maxDelayMs < $this->baseDelayMs) {
            throw new ConfigurationError('Retry maxDelayMs must be greater than or equal to baseDelayMs.', errorCode: 'sdk_configuration_error');
        }
    }
}
