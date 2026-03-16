<!--
     ▄▄▄▄
   ▄█▀▀███▄▄              █▄
   ██    ██ ▄             ██
   ██    ██ ████▄▄█▀█▄ ▄████ ▄█▀█▄▀██ ██▀
   ██  ▄ ██ ██   ██▄█▀ ██ ██ ██▄█▀  ███
    ▀█████▄▄█▀  ▄▀█▄▄▄▄█▀███▄▀█▄▄▄▄██ ██▄
         ▀█

   Copyright (C) 2026 — 2026, Qredex, LTD. All Rights Reserved.

   DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.

   Licensed under the Apache License, Version 2.0. See LICENSE for the full license text.
   You may not use this file except in compliance with that License.
   Unless required by applicable law or agreed to in writing, software distributed under the
   License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
   either express or implied. See the License for the specific language governing permissions
   and limitations under the License.

   If you need additional information or have any questions, please email: copyright@qredex.com
-->

# API Reference

## Client Initialization

- `Qredex::init(QredexConfig $config): Qredex`
- `QredexConfig::fromEnvironment(...): QredexConfig`
- `Qredex::bootstrap(...): Qredex`
  Deprecated as the primary documented initialization path. Kept for backward compatibility.

## Canonical Write Requests

These request classes are the preferred public input surface:

- `Qredex\Request\CreateCreatorRequest`
- `Qredex\Request\CreateLinkRequest`
- `Qredex\Request\IssueInfluenceIntentTokenRequest`
- `Qredex\Request\LockPurchaseIntentRequest`
- `Qredex\Request\RecordPaidOrderRequest`
- `Qredex\Request\RecordRefundRequest`

Each canonical write method still accepts legacy arrays for backward compatibility.

## Scopes

- `Qredex\Auth\QredexScope`

Programmatic configuration should prefer enum values or enum arrays over raw scope strings.

## Resources

### Creators

- `creators()->create(array|CreateCreatorRequest): Creator`
- `creators()->get(string $creatorId): Creator`
- `creators()->list(array $filters = []): Page<Creator>`

### Links

- `links()->create(array|CreateLinkRequest): Link`
- `links()->get(string $linkId): Link`
- `links()->list(array $filters = []): Page<Link>`
- `links()->getStats(string $linkId): LinkStats`

### Intents

- `intents()->issueInfluenceIntentToken(array|IssueInfluenceIntentTokenRequest): InfluenceIntent`
- `intents()->lockPurchaseIntent(array|LockPurchaseIntentRequest): PurchaseIntent`
- `intents()->get(string $pit): PurchaseIntent`
  Deprecated from the default happy path. Keep for operational support use cases.
- `intents()->getByTokenId(string $tokenId): PurchaseIntent`
  Deprecated from the default happy path. Keep for operational support use cases.
- `intents()->getByInfluenceIntentToken(string $iit): PurchaseIntent`
  Deprecated from the default happy path. Keep for operational support use cases.
- `intents()->latestUnlocked(int $hours = 24): PurchaseIntent`
  Deprecated from the default happy path. Keep for operational support use cases.

### Orders

- `orders()->recordPaidOrder(array|RecordPaidOrderRequest): OrderAttribution`
- `orders()->list(array $filters = []): Page<OrderAttribution>`
- `orders()->getDetails(string $orderAttributionId): OrderAttribution`

### Refunds

- `refunds()->recordRefund(array|RecordRefundRequest): OrderAttribution`

## Auth

- `auth()->issueToken(string|array|null $scope = null): OAuthToken`
- `auth()->clearTokenCache(): void`

This surface remains available for diagnostics and migration paths, but canonical integrations should rely on automatic token management.
