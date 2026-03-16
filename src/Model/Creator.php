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

namespace Qredex\Model;

use JsonSerializable;
use Qredex\Internal\ArrayMapper;

final readonly class Creator implements JsonSerializable
{
    /**
     * @param array<string, string> $socials
     */
    public function __construct(
        public string $id,
        public string $handle,
        public string $status,
        public ?string $displayName,
        public ?string $email,
        public array $socials,
        public string $createdAt,
        public string $updatedAt,
        public ?int $linksCount = null,
        public ?int $ordersCount = null,
        public ?float $revenueTotal = null,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            id: ArrayMapper::string($payload, 'id'),
            handle: ArrayMapper::string($payload, 'handle'),
            status: ArrayMapper::string($payload, 'status'),
            displayName: ArrayMapper::nullableString($payload, 'display_name'),
            email: ArrayMapper::nullableString($payload, 'email'),
            socials: ArrayMapper::stringMap($payload, 'socials'),
            createdAt: ArrayMapper::string($payload, 'created_at'),
            updatedAt: ArrayMapper::string($payload, 'updated_at'),
            linksCount: ArrayMapper::nullableInt($payload, 'links_count'),
            ordersCount: ArrayMapper::nullableInt($payload, 'orders_count'),
            revenueTotal: ArrayMapper::nullableFloat($payload, 'revenue_total'),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'handle' => $this->handle,
            'status' => $this->status,
            'display_name' => $this->displayName,
            'email' => $this->email,
            'socials' => $this->socials,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'links_count' => $this->linksCount,
            'orders_count' => $this->ordersCount,
            'revenue_total' => $this->revenueTotal,
        ];
    }
}
