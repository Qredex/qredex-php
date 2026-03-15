<?php

declare(strict_types=1);

namespace Qredex\Model;

use JsonSerializable;
use Qredex\Internal\ArrayMapper;

/**
 * @template T
 *
 * @implements JsonSerializable<array<string, mixed>>
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
