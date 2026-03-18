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

## Canonical Write Requests

These request classes are the preferred public input surface:

- `Qredex\Request\CreateCreatorRequest`
- `Qredex\Request\CreateLinkRequest`
- `Qredex\Request\IssueInfluenceIntentTokenRequest`
- `Qredex\Request\LockPurchaseIntentRequest`
- `Qredex\Request\RecordPaidOrderRequest`
- `Qredex\Request\RecordRefundRequest`

Each canonical write method still accepts legacy arrays for backward compatibility.

## Typed List Filters

- `Qredex\Request\ListCreatorsFilter`
- `Qredex\Request\ListLinksFilter`
- `Qredex\Request\ListOrdersFilter`

List methods accept both typed filters and legacy arrays.

## Scopes

- `Qredex\Auth\QredexScope`

Programmatic configuration should prefer enum values or enum arrays over raw scope strings.

## Resources

### Creators

- `creators()->create(CreateCreatorRequest): Creator` as the canonical public path
- `creators()->get(string $creatorId): Creator`
- `creators()->list(ListCreatorsFilter|array $filters = []): Page<Creator>` with typed filters preferred

### Links

- `links()->create(CreateLinkRequest): Link` as the canonical public path
- `links()->get(string $linkId): Link`
- `links()->list(ListLinksFilter|array $filters = []): Page<Link>` with typed filters preferred
- `links()->getStats(string $linkId): LinkStats`

### Intents

- `intents()->issueInfluenceIntentToken(IssueInfluenceIntentTokenRequest): InfluenceIntent`
- `intents()->lockPurchaseIntent(LockPurchaseIntentRequest): PurchaseIntent`

### Orders

- `orders()->recordPaidOrder(RecordPaidOrderRequest): OrderAttribution`
- `orders()->list(ListOrdersFilter|array $filters = []): Page<OrderAttribution>` with typed filters preferred
- `orders()->getDetails(string $orderAttributionId): OrderAttribution`

### Refunds

- `refunds()->recordRefund(RecordRefundRequest): OrderAttribution`
