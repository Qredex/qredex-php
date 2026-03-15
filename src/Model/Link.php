<?php

declare(strict_types=1);

namespace Qredex\Model;

use JsonSerializable;
use Qredex\Internal\ArrayMapper;

final readonly class Link implements JsonSerializable
{
    public function __construct(
        public string $id,
        public ?string $merchantId,
        public string $storeId,
        public string $creatorId,
        public string $linkName,
        public string $linkCode,
        public ?string $publicLinkUrl,
        public string $destinationPath,
        public ?string $note,
        public string $status,
        public int $attributionWindowDays,
        public ?string $linkExpiryAt,
        public ?string $disabledAt,
        public ?string $discountCode,
        public string $createdAt,
        public string $updatedAt,
        public ?string $creatorHandle = null,
        public ?string $creatorDisplayName = null,
        public ?int $clicksCount = null,
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
            merchantId: ArrayMapper::nullableString($payload, 'merchant_id'),
            storeId: ArrayMapper::string($payload, 'store_id'),
            creatorId: ArrayMapper::string($payload, 'creator_id'),
            linkName: ArrayMapper::string($payload, 'link_name'),
            linkCode: ArrayMapper::string($payload, 'link_code'),
            publicLinkUrl: ArrayMapper::nullableString($payload, 'public_link_url'),
            destinationPath: ArrayMapper::string($payload, 'destination_path'),
            note: ArrayMapper::nullableString($payload, 'note'),
            status: ArrayMapper::string($payload, 'status'),
            attributionWindowDays: ArrayMapper::int($payload, 'attribution_window_days'),
            linkExpiryAt: ArrayMapper::nullableString($payload, 'link_expiry_at'),
            disabledAt: ArrayMapper::nullableString($payload, 'disabled_at'),
            discountCode: ArrayMapper::nullableString($payload, 'discount_code'),
            createdAt: ArrayMapper::string($payload, 'created_at'),
            updatedAt: ArrayMapper::string($payload, 'updated_at'),
            creatorHandle: ArrayMapper::nullableString($payload, 'creator_handle'),
            creatorDisplayName: ArrayMapper::nullableString($payload, 'creator_display_name'),
            clicksCount: ArrayMapper::nullableInt($payload, 'clicks_count'),
            ordersCount: ArrayMapper::nullableInt($payload, 'orders_count'),
            revenueTotal: ArrayMapper::nullableFloat($payload, 'revenue_total'),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'merchant_id' => $this->merchantId,
            'store_id' => $this->storeId,
            'creator_id' => $this->creatorId,
            'link_name' => $this->linkName,
            'link_code' => $this->linkCode,
            'public_link_url' => $this->publicLinkUrl,
            'destination_path' => $this->destinationPath,
            'note' => $this->note,
            'status' => $this->status,
            'attribution_window_days' => $this->attributionWindowDays,
            'link_expiry_at' => $this->linkExpiryAt,
            'disabled_at' => $this->disabledAt,
            'discount_code' => $this->discountCode,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'creator_handle' => $this->creatorHandle,
            'creator_display_name' => $this->creatorDisplayName,
            'clicks_count' => $this->clicksCount,
            'orders_count' => $this->ordersCount,
            'revenue_total' => $this->revenueTotal,
        ];
    }
}
