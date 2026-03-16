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

namespace Qredex\Resource;

use Qredex\Internal\HttpClient;
use Qredex\Internal\Validator;
use Qredex\Model\InfluenceIntent;
use Qredex\Model\PurchaseIntent;
use Qredex\Request\IssueInfluenceIntentTokenRequest;
use Qredex\Request\LockPurchaseIntentRequest;

final readonly class IntentsClient
{
    public function __construct(private HttpClient $http)
    {
    }

    /**
     * @param array<string, mixed>|IssueInfluenceIntentTokenRequest $payload
     */
    public function issueInfluenceIntentToken(array|IssueInfluenceIntentTokenRequest $payload): InfluenceIntent
    {
        $payload = $payload instanceof IssueInfluenceIntentTokenRequest ? $payload->toArray() : $payload;
        Validator::issueInfluenceIntentToken($payload);

        return InfluenceIntent::fromArray(
            $this->http->json('POST', '/api/v1/integrations/intents/token', body: $payload),
        );
    }

    /**
     * @param array<string, mixed>|LockPurchaseIntentRequest $payload
     */
    public function lockPurchaseIntent(array|LockPurchaseIntentRequest $payload): PurchaseIntent
    {
        $payload = $payload instanceof LockPurchaseIntentRequest ? $payload->toArray() : $payload;
        Validator::lockPurchaseIntent($payload);

        return PurchaseIntent::fromArray(
            $this->http->json('POST', '/api/v1/integrations/intents/lock', body: $payload),
        );
    }

    /**
     * @deprecated Prefer the canonical write flow. Keep intent lookups for operational support use cases only.
     */
    public function get(string $pit): PurchaseIntent
    {
        Validator::nonEmptyString($pit, 'pit');

        return PurchaseIntent::fromArray(
            $this->http->json('GET', '/api/v1/integrations/intents/' . rawurlencode($pit)),
        );
    }

    /**
     * @deprecated Prefer the canonical write flow. Keep intent lookups for operational support use cases only.
     */
    public function getByTokenId(string $tokenId): PurchaseIntent
    {
        Validator::uuid($tokenId, 'tokenId');

        return PurchaseIntent::fromArray(
            $this->http->json('GET', "/api/v1/integrations/intents/by-token-id/{$tokenId}"),
        );
    }

    /**
     * @deprecated Prefer the canonical write flow. Keep intent lookups for operational support use cases only.
     */
    public function getByInfluenceIntentToken(string $iit): PurchaseIntent
    {
        Validator::nonEmptyString($iit, 'iit');

        return PurchaseIntent::fromArray(
            $this->http->json('GET', '/api/v1/integrations/intents/by-iit/' . rawurlencode($iit)),
        );
    }

    /**
     * @deprecated Prefer the canonical write flow. Keep intent lookups for operational support use cases only.
     */
    public function latestUnlocked(int $hours = 24): PurchaseIntent
    {
        Validator::latestUnlocked(['hours' => $hours]);

        return PurchaseIntent::fromArray(
            $this->http->json('GET', '/api/v1/integrations/intents/latest-unlocked', query: ['hours' => $hours]),
        );
    }
}
