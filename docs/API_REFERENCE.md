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

- `creators()->create(array|CreateCreatorRequest): Creator`
- `creators()->get(string $creatorId): Creator`
- `creators()->list(array|ListCreatorsFilter $filters = []): Page<Creator>`

### Links

- `links()->create(array|CreateLinkRequest): Link`
- `links()->get(string $linkId): Link`
- `links()->list(array|ListLinksFilter $filters = []): Page<Link>`
- `links()->getStats(string $linkId): LinkStats`

### Intents

- `intents()->issueInfluenceIntentToken(array|IssueInfluenceIntentTokenRequest): InfluenceIntent`
- `intents()->lockPurchaseIntent(array|LockPurchaseIntentRequest): PurchaseIntent`

### Orders

- `orders()->recordPaidOrder(array|RecordPaidOrderRequest): OrderAttribution`
- `orders()->list(array|ListOrdersFilter $filters = []): Page<OrderAttribution>`
- `orders()->getDetails(string $orderAttributionId): OrderAttribution`

### Refunds

- `refunds()->recordRefund(array|RecordRefundRequest): OrderAttribution`
