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

use Qredex\Error\ApiValidationError;
use Qredex\Error\AuthenticationError;
use Qredex\Error\AuthorizationError;
use Qredex\Error\ConflictError;
use Qredex\Error\NetworkError;
use Qredex\Error\RequestValidationError;
use Qredex\Error\ResponseDecodingError;
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
     *
     * @throws RequestValidationError
     * @throws AuthenticationError
     * @throws AuthorizationError
     * @throws ApiValidationError
     * @throws ConflictError
     * @throws NetworkError
     * @throws ResponseDecodingError
     */
    public function issueInfluenceIntentToken(array|IssueInfluenceIntentTokenRequest $payload): InfluenceIntent
    {
        if ($payload instanceof IssueInfluenceIntentTokenRequest) {
            $payload = $payload->toArray();
        } else {
            Validator::issueInfluenceIntentToken($payload);
        }

        return InfluenceIntent::fromArray(
            $this->http->json('POST', '/api/v1/integrations/intents/token', body: $payload),
        );
    }

    /**
     * @param array<string, mixed>|LockPurchaseIntentRequest $payload
     *
     * @throws RequestValidationError
     * @throws AuthenticationError
     * @throws AuthorizationError
     * @throws ApiValidationError
     * @throws ConflictError
     * @throws NetworkError
     * @throws ResponseDecodingError
     */
    public function lockPurchaseIntent(array|LockPurchaseIntentRequest $payload): PurchaseIntent
    {
        if ($payload instanceof LockPurchaseIntentRequest) {
            $payload = $payload->toArray();
        } else {
            Validator::lockPurchaseIntent($payload);
        }

        return PurchaseIntent::fromArray(
            $this->http->json('POST', '/api/v1/integrations/intents/lock', body: $payload),
        );
    }
}
