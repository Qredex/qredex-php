<?php

declare(strict_types=1);

namespace Qredex\Resource;

use Qredex\Internal\HttpClient;
use Qredex\Internal\Validator;
use Qredex\Model\InfluenceIntent;
use Qredex\Model\PurchaseIntent;

final readonly class IntentsClient
{
    public function __construct(private HttpClient $http)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function issueInfluenceIntentToken(array $payload): InfluenceIntent
    {
        Validator::issueInfluenceIntentToken($payload);

        return InfluenceIntent::fromArray(
            $this->http->json('POST', '/api/v1/integrations/intents/token', body: $payload),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function lockPurchaseIntent(array $payload): PurchaseIntent
    {
        Validator::lockPurchaseIntent($payload);

        return PurchaseIntent::fromArray(
            $this->http->json('POST', '/api/v1/integrations/intents/lock', body: $payload),
        );
    }

    public function get(string $pit): PurchaseIntent
    {
        Validator::nonEmptyString($pit, 'pit');

        return PurchaseIntent::fromArray(
            $this->http->json('GET', '/api/v1/integrations/intents/' . rawurlencode($pit)),
        );
    }

    public function getByTokenId(string $tokenId): PurchaseIntent
    {
        Validator::uuid($tokenId, 'tokenId');

        return PurchaseIntent::fromArray(
            $this->http->json('GET', "/api/v1/integrations/intents/by-token-id/{$tokenId}"),
        );
    }

    public function getByInfluenceIntentToken(string $iit): PurchaseIntent
    {
        Validator::nonEmptyString($iit, 'iit');

        return PurchaseIntent::fromArray(
            $this->http->json('GET', '/api/v1/integrations/intents/by-iit/' . rawurlencode($iit)),
        );
    }

    public function latestUnlocked(int $hours = 24): PurchaseIntent
    {
        Validator::latestUnlocked(['hours' => $hours]);

        return PurchaseIntent::fromArray(
            $this->http->json('GET', '/api/v1/integrations/intents/latest-unlocked', query: ['hours' => $hours]),
        );
    }
}
