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

final readonly class PurchaseIntent implements JsonSerializable
{
    public function __construct(
        public string $id,
        public string $merchantId,
        public string $storeId,
        public string $linkId,
        public ?string $influenceIntentId,
        public string $token,
        public string $tokenId,
        public ?string $source,
        public ?string $originMatchStatus,
        public ?string $windowStatus,
        public ?int $attributionWindowDays,
        public ?int $attributionWindowDaysSnapshot,
        public string $storeDomainSnapshot,
        public ?string $linkExpiryAtSnapshot,
        public ?string $discountCodeSnapshot,
        public string $issuedAt,
        public string $expiresAt,
        public ?string $lockedAt,
        public int $integrityVersion,
        public ?bool $eligible = null,
        public ?Timing $timing = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        $timing = ArrayMapper::nullableArray($payload, 'timing');

        return new self(
            id: ArrayMapper::string($payload, 'id'),
            merchantId: ArrayMapper::string($payload, 'merchant_id'),
            storeId: ArrayMapper::string($payload, 'store_id'),
            linkId: ArrayMapper::string($payload, 'link_id'),
            influenceIntentId: ArrayMapper::nullableString($payload, 'influence_intent_id'),
            token: ArrayMapper::string($payload, 'token'),
            tokenId: ArrayMapper::string($payload, 'token_id'),
            source: ArrayMapper::nullableString($payload, 'source'),
            originMatchStatus: ArrayMapper::nullableString($payload, 'origin_match_status'),
            windowStatus: ArrayMapper::nullableString($payload, 'window_status'),
            attributionWindowDays: ArrayMapper::nullableInt($payload, 'attribution_window_days'),
            attributionWindowDaysSnapshot: ArrayMapper::nullableInt($payload, 'attribution_window_days_snapshot'),
            storeDomainSnapshot: ArrayMapper::string($payload, 'store_domain_snapshot'),
            linkExpiryAtSnapshot: ArrayMapper::nullableString($payload, 'link_expiry_at_snapshot'),
            discountCodeSnapshot: ArrayMapper::nullableString($payload, 'discount_code_snapshot'),
            issuedAt: ArrayMapper::string($payload, 'issued_at'),
            expiresAt: ArrayMapper::string($payload, 'expires_at'),
            lockedAt: ArrayMapper::nullableString($payload, 'locked_at'),
            integrityVersion: ArrayMapper::int($payload, 'integrity_version'),
            eligible: array_key_exists('eligible', $payload) && is_bool($payload['eligible']) ? $payload['eligible'] : null,
            timing: $timing === null ? null : Timing::fromArray($timing),
            createdAt: ArrayMapper::nullableString($payload, 'created_at'),
            updatedAt: ArrayMapper::nullableString($payload, 'updated_at'),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'merchant_id' => $this->merchantId,
            'store_id' => $this->storeId,
            'link_id' => $this->linkId,
            'influence_intent_id' => $this->influenceIntentId,
            'token' => $this->token,
            'token_id' => $this->tokenId,
            'source' => $this->source,
            'origin_match_status' => $this->originMatchStatus,
            'window_status' => $this->windowStatus,
            'attribution_window_days' => $this->attributionWindowDays,
            'attribution_window_days_snapshot' => $this->attributionWindowDaysSnapshot,
            'store_domain_snapshot' => $this->storeDomainSnapshot,
            'link_expiry_at_snapshot' => $this->linkExpiryAtSnapshot,
            'discount_code_snapshot' => $this->discountCodeSnapshot,
            'issued_at' => $this->issuedAt,
            'expires_at' => $this->expiresAt,
            'locked_at' => $this->lockedAt,
            'integrity_version' => $this->integrityVersion,
            'eligible' => $this->eligible,
            'timing' => $this->timing,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
