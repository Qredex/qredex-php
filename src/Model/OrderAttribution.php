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

final readonly class OrderAttribution implements JsonSerializable
{
    /**
     * @param array<int, OrderAttributionTimelineEvent> $timeline
     */
    public function __construct(
        public string $id,
        public ?string $merchantId,
        public string $orderSource,
        public string $externalOrderId,
        public ?string $orderNumber,
        public ?string $paidAt,
        public string $currency,
        public ?float $subtotalPrice,
        public ?float $discountTotal,
        public ?float $totalPrice,
        public ?string $purchaseIntentToken,
        public ?string $linkId,
        public ?string $linkName,
        public ?string $linkCode,
        public ?string $creatorId,
        public ?string $creatorHandle,
        public ?string $creatorDisplayName,
        public bool $duplicateSuspect,
        public ?string $duplicateConfidence,
        public ?string $duplicateReason,
        public ?string $duplicateOfOrderAttributionId,
        public ?string $attributionLockedAt,
        public ?int $attributionWindowDays,
        public ?string $windowStatus,
        public ?string $tokenIntegrity,
        public ?string $integrityReason,
        public ?string $originMatchStatus,
        public int $integrityScore,
        public string $integrityBand,
        public bool $reviewRequired,
        public string $resolutionStatus,
        public ?OrderAttributionScoreBreakdown $scoreBreakdown,
        public array $timeline,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        $scoreBreakdownPayload = ArrayMapper::nullableArray($payload, 'score_breakdown_json');
        $timeline = [];

        foreach (ArrayMapper::list($payload, 'timeline') as $item) {
            if (is_array($item)) {
                $timeline[] = OrderAttributionTimelineEvent::fromArray($item);
            }
        }

        return new self(
            id: ArrayMapper::string($payload, 'id'),
            merchantId: ArrayMapper::nullableString($payload, 'merchant_id'),
            orderSource: ArrayMapper::string($payload, 'order_source'),
            externalOrderId: ArrayMapper::string($payload, 'external_order_id'),
            orderNumber: ArrayMapper::nullableString($payload, 'order_number'),
            paidAt: ArrayMapper::nullableString($payload, 'paid_at'),
            currency: ArrayMapper::string($payload, 'currency'),
            subtotalPrice: ArrayMapper::nullableFloat($payload, 'subtotal_price'),
            discountTotal: ArrayMapper::nullableFloat($payload, 'discount_total'),
            totalPrice: ArrayMapper::nullableFloat($payload, 'total_price'),
            purchaseIntentToken: ArrayMapper::nullableString($payload, 'purchase_intent_token'),
            linkId: ArrayMapper::nullableString($payload, 'link_id'),
            linkName: ArrayMapper::nullableString($payload, 'link_name'),
            linkCode: ArrayMapper::nullableString($payload, 'link_code'),
            creatorId: ArrayMapper::nullableString($payload, 'creator_id'),
            creatorHandle: ArrayMapper::nullableString($payload, 'creator_handle'),
            creatorDisplayName: ArrayMapper::nullableString($payload, 'creator_display_name'),
            duplicateSuspect: ArrayMapper::bool($payload, 'duplicate_suspect'),
            duplicateConfidence: ArrayMapper::nullableString($payload, 'duplicate_confidence'),
            duplicateReason: ArrayMapper::nullableString($payload, 'duplicate_reason'),
            duplicateOfOrderAttributionId: ArrayMapper::nullableString($payload, 'duplicate_of_order_attribution_id'),
            attributionLockedAt: ArrayMapper::nullableString($payload, 'attribution_locked_at'),
            attributionWindowDays: ArrayMapper::nullableInt($payload, 'attribution_window_days'),
            windowStatus: ArrayMapper::nullableString($payload, 'window_status'),
            tokenIntegrity: ArrayMapper::nullableString($payload, 'token_integrity'),
            integrityReason: ArrayMapper::nullableString($payload, 'integrity_reason'),
            originMatchStatus: ArrayMapper::nullableString($payload, 'origin_match_status'),
            integrityScore: ArrayMapper::int($payload, 'integrity_score'),
            integrityBand: ArrayMapper::string($payload, 'integrity_band'),
            reviewRequired: ArrayMapper::bool($payload, 'review_required'),
            resolutionStatus: ArrayMapper::string($payload, 'resolution_status'),
            scoreBreakdown: $scoreBreakdownPayload === null ? null : OrderAttributionScoreBreakdown::fromArray($scoreBreakdownPayload),
            timeline: $timeline,
            createdAt: ArrayMapper::string($payload, 'created_at'),
            updatedAt: ArrayMapper::string($payload, 'updated_at'),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'merchant_id' => $this->merchantId,
            'order_source' => $this->orderSource,
            'external_order_id' => $this->externalOrderId,
            'order_number' => $this->orderNumber,
            'paid_at' => $this->paidAt,
            'currency' => $this->currency,
            'subtotal_price' => $this->subtotalPrice,
            'discount_total' => $this->discountTotal,
            'total_price' => $this->totalPrice,
            'purchase_intent_token' => $this->purchaseIntentToken,
            'link_id' => $this->linkId,
            'link_name' => $this->linkName,
            'link_code' => $this->linkCode,
            'creator_id' => $this->creatorId,
            'creator_handle' => $this->creatorHandle,
            'creator_display_name' => $this->creatorDisplayName,
            'duplicate_suspect' => $this->duplicateSuspect,
            'duplicate_confidence' => $this->duplicateConfidence,
            'duplicate_reason' => $this->duplicateReason,
            'duplicate_of_order_attribution_id' => $this->duplicateOfOrderAttributionId,
            'attribution_locked_at' => $this->attributionLockedAt,
            'attribution_window_days' => $this->attributionWindowDays,
            'window_status' => $this->windowStatus,
            'token_integrity' => $this->tokenIntegrity,
            'integrity_reason' => $this->integrityReason,
            'origin_match_status' => $this->originMatchStatus,
            'integrity_score' => $this->integrityScore,
            'integrity_band' => $this->integrityBand,
            'review_required' => $this->reviewRequired,
            'resolution_status' => $this->resolutionStatus,
            'score_breakdown_json' => $this->scoreBreakdown,
            'timeline' => $this->timeline,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
