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

final readonly class LinkStats implements JsonSerializable
{
    public function __construct(
        public string $linkId,
        public int $clicksCount,
        public int $sessionsCount,
        public int $ordersCount,
        public ?float $revenueTotal,
        public int $tokenInvalidCount,
        public int $tokenMissingCount,
        public ?string $lastClickAt,
        public ?string $lastOrderAt,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            linkId: ArrayMapper::string($payload, 'link_id'),
            clicksCount: ArrayMapper::int($payload, 'clicks_count'),
            sessionsCount: ArrayMapper::int($payload, 'sessions_count'),
            ordersCount: ArrayMapper::int($payload, 'orders_count'),
            revenueTotal: ArrayMapper::nullableFloat($payload, 'revenue_total'),
            tokenInvalidCount: ArrayMapper::int($payload, 'token_invalid_count'),
            tokenMissingCount: ArrayMapper::int($payload, 'token_missing_count'),
            lastClickAt: ArrayMapper::nullableString($payload, 'last_click_at'),
            lastOrderAt: ArrayMapper::nullableString($payload, 'last_order_at'),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'link_id' => $this->linkId,
            'clicks_count' => $this->clicksCount,
            'sessions_count' => $this->sessionsCount,
            'orders_count' => $this->ordersCount,
            'revenue_total' => $this->revenueTotal,
            'token_invalid_count' => $this->tokenInvalidCount,
            'token_missing_count' => $this->tokenMissingCount,
            'last_click_at' => $this->lastClickAt,
            'last_order_at' => $this->lastOrderAt,
        ];
    }
}
