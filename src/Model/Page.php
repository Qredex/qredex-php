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

/**
 * @template T
 */
final readonly class Page implements JsonSerializable
{
    /**
     * @param array<int, T> $items
     */
    public function __construct(
        public array $items,
        public int $page,
        public int $size,
        public int $totalElements,
        public int $totalPages,
    ) {
    }

    /**
     * @template TValue
     *
     * @param array<string, mixed> $payload
     * @param callable(array<string, mixed>): TValue $itemFactory
     * @return self<TValue>
     */
    public static function fromArray(array $payload, callable $itemFactory): self
    {
        $items = [];

        foreach (ArrayMapper::list($payload, 'items') as $item) {
            if (is_array($item)) {
                $items[] = $itemFactory($item);
            }
        }

        return new self(
            items: $items,
            page: ArrayMapper::int($payload, 'page'),
            size: ArrayMapper::int($payload, 'size'),
            totalElements: ArrayMapper::int($payload, 'total_elements'),
            totalPages: ArrayMapper::int($payload, 'total_pages'),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'items' => $this->items,
            'page' => $this->page,
            'size' => $this->size,
            'total_elements' => $this->totalElements,
            'total_pages' => $this->totalPages,
        ];
    }
}
