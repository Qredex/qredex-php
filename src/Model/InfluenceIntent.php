<?php

declare(strict_types=1);

namespace Qredex\Model;

use JsonSerializable;
use Qredex\Internal\ArrayMapper;

final readonly class InfluenceIntent implements JsonSerializable
{
    public function __construct(
        public string $id,
        public string $merchantId,
        public string $linkId,
        public string $token,
        public string $tokenId,
        public string $issuedAt,
        public string $expiresAt,
        public string $status,
        public int $integrityVersion,
        public ?string $ipHash = null,
        public ?string $userAgentHash = null,
        public ?string $referrer = null,
        public ?string $landingPath = null,
        public ?Timing $timing = null,
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
            linkId: ArrayMapper::string($payload, 'link_id'),
            token: ArrayMapper::string($payload, 'token'),
            tokenId: ArrayMapper::string($payload, 'token_id'),
            issuedAt: ArrayMapper::string($payload, 'issued_at'),
            expiresAt: ArrayMapper::string($payload, 'expires_at'),
            status: ArrayMapper::string($payload, 'status'),
            integrityVersion: ArrayMapper::int($payload, 'integrity_version'),
            ipHash: ArrayMapper::nullableString($payload, 'ip_hash'),
            userAgentHash: ArrayMapper::nullableString($payload, 'user_agent_hash'),
            referrer: ArrayMapper::nullableString($payload, 'referrer'),
            landingPath: ArrayMapper::nullableString($payload, 'landing_path'),
            timing: $timing === null ? null : Timing::fromArray($timing),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'merchant_id' => $this->merchantId,
            'link_id' => $this->linkId,
            'token' => $this->token,
            'token_id' => $this->tokenId,
            'issued_at' => $this->issuedAt,
            'expires_at' => $this->expiresAt,
            'status' => $this->status,
            'integrity_version' => $this->integrityVersion,
            'ip_hash' => $this->ipHash,
            'user_agent_hash' => $this->userAgentHash,
            'referrer' => $this->referrer,
            'landing_path' => $this->landingPath,
            'timing' => $this->timing,
        ];
    }
}
