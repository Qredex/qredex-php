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

namespace Qredex\Auth;

use Qredex\Error\ConfigurationError;

final readonly class ClientCredentialsAuthentication
{
    /**
     * @param string|QredexScope|list<string|QredexScope>|null $scope
     */
    public function __construct(
        public string $clientId,
        public string $clientSecret,
        public string|QredexScope|array|null $scope = null,
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

        if ($this->scope instanceof QredexScope) {
            return $this->scope->value;
        }

        if (is_string($this->scope)) {
            $scope = preg_replace('/[\s,]+/', ' ', trim($this->scope));

            return $scope === '' ? null : $scope;
        }

        $parts = array_values(array_filter(array_map(
            static fn (mixed $value): string => trim($value instanceof QredexScope ? $value->value : (string) $value),
            $this->scope,
        ), static fn (string $value): bool => $value !== ''));

        return $parts === [] ? null : implode(' ', $parts);
    }
}
