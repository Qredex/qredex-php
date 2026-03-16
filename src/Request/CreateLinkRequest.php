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

final readonly class CreateLinkRequest implements JsonSerializable
{
    public function __construct(
        public string $storeId,
        public string $creatorId,
        public string $linkName,
        public string $destinationPath,
        public ?int $attributionWindowDays = null,
        public ?string $note = null,
        public ?string $linkExpiryAt = null,
        public ?string $discountCode = null,
        public ?string $status = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = array_filter([
            'store_id' => $this->storeId,
            'creator_id' => $this->creatorId,
            'link_name' => $this->linkName,
            'destination_path' => $this->destinationPath,
            'attribution_window_days' => $this->attributionWindowDays,
            'note' => $this->note,
            'link_expiry_at' => $this->linkExpiryAt,
            'discount_code' => $this->discountCode,
            'status' => $this->status,
        ], static fn (mixed $value): bool => $value !== null);

        Validator::createLink($payload);

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
