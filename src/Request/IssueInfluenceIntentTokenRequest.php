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

namespace Qredex\Request;

use JsonSerializable;
use Qredex\Internal\Validator;

final readonly class IssueInfluenceIntentTokenRequest implements JsonSerializable
{
    public function __construct(
        public string $linkId,
        public ?string $ipHash = null,
        public ?string $userAgentHash = null,
        public ?string $referrer = null,
        public ?string $landingPath = null,
        public ?string $expiresAt = null,
        public ?int $integrityVersion = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = array_filter([
            'link_id' => $this->linkId,
            'ip_hash' => $this->ipHash,
            'user_agent_hash' => $this->userAgentHash,
            'referrer' => $this->referrer,
            'landing_path' => $this->landingPath,
            'expires_at' => $this->expiresAt,
            'integrity_version' => $this->integrityVersion,
        ], static fn (mixed $value): bool => $value !== null);

        Validator::issueInfluenceIntentToken($payload);

        return $payload;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
