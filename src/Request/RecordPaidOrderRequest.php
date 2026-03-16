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

final readonly class RecordPaidOrderRequest implements JsonSerializable
{
    public function __construct(
        public string $storeId,
        public string $externalOrderId,
        public string $currency,
        public ?string $orderNumber = null,
        public ?string $paidAt = null,
        public int|float|null $subtotalPrice = null,
        public int|float|null $discountTotal = null,
        public int|float|null $totalPrice = null,
        public ?string $customerEmailHash = null,
        public ?string $checkoutToken = null,
        public ?string $purchaseIntentToken = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = array_filter([
            'store_id' => $this->storeId,
            'external_order_id' => $this->externalOrderId,
            'currency' => $this->currency,
            'order_number' => $this->orderNumber,
            'paid_at' => $this->paidAt,
            'subtotal_price' => $this->subtotalPrice,
            'discount_total' => $this->discountTotal,
            'total_price' => $this->totalPrice,
            'customer_email_hash' => $this->customerEmailHash,
            'checkout_token' => $this->checkoutToken,
            'purchase_intent_token' => $this->purchaseIntentToken,
        ], static fn (mixed $value): bool => $value !== null);

        Validator::recordPaidOrder($payload);

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
