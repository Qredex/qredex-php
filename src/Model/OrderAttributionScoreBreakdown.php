<?php

declare(strict_types=1);

namespace Qredex\Model;

use JsonSerializable;
use Qredex\Internal\ArrayMapper;

final readonly class OrderAttributionScoreBreakdown implements JsonSerializable
{
    /**
     * @param array<int, string> $reviewReasons
     */
    public function __construct(
        public ?int $scoreVersion,
        public ?int $baseScore,
        public ?int $originAdjustment,
        public ?int $duplicateAdjustment,
        public ?int $finalScore,
        public ?string $tokenIntegrity,
        public ?string $integrityReason,
        public ?string $windowStatus,
        public string $resolutionStatus,
        public ?string $originMatchStatus,
        public ?string $duplicateConfidence,
        public ?bool $reviewRequired,
        public array $reviewReasons,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        $reviewReasons = [];

        foreach (ArrayMapper::list($payload, 'review_reasons') as $value) {
            if (is_string($value)) {
                $reviewReasons[] = $value;
            }
        }

        return new self(
            scoreVersion: ArrayMapper::nullableInt($payload, 'score_version'),
            baseScore: ArrayMapper::nullableInt($payload, 'base_score'),
            originAdjustment: ArrayMapper::nullableInt($payload, 'origin_adjustment'),
            duplicateAdjustment: ArrayMapper::nullableInt($payload, 'duplicate_adjustment'),
            finalScore: ArrayMapper::nullableInt($payload, 'final_score'),
            tokenIntegrity: ArrayMapper::nullableString($payload, 'token_integrity'),
            integrityReason: ArrayMapper::nullableString($payload, 'integrity_reason'),
            windowStatus: ArrayMapper::nullableString($payload, 'window_status'),
            resolutionStatus: ArrayMapper::string($payload, 'resolution_status'),
            originMatchStatus: ArrayMapper::nullableString($payload, 'origin_match_status'),
            duplicateConfidence: ArrayMapper::nullableString($payload, 'duplicate_confidence'),
            reviewRequired: array_key_exists('review_required', $payload) && is_bool($payload['review_required']) ? $payload['review_required'] : null,
            reviewReasons: $reviewReasons,
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'score_version' => $this->scoreVersion,
            'base_score' => $this->baseScore,
            'origin_adjustment' => $this->originAdjustment,
            'duplicate_adjustment' => $this->duplicateAdjustment,
            'final_score' => $this->finalScore,
            'token_integrity' => $this->tokenIntegrity,
            'integrity_reason' => $this->integrityReason,
            'window_status' => $this->windowStatus,
            'resolution_status' => $this->resolutionStatus,
            'origin_match_status' => $this->originMatchStatus,
            'duplicate_confidence' => $this->duplicateConfidence,
            'review_required' => $this->reviewRequired,
            'review_reasons' => $this->reviewReasons,
        ];
    }
}
