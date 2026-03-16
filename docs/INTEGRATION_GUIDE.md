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

# Integration Guide

## Scope

This SDK targets only the Qredex Integrations API:

- `/api/v1/auth/token`
- `/api/v1/integrations/creators/**`
- `/api/v1/integrations/links/**`
- `/api/v1/integrations/intents/**`
- `/api/v1/integrations/orders/**`

It does not cover:

- `/api/v1/merchant/**`
- `/api/v1/internal/**`
- browser/runtime logic
- Shopify embedded/session flows
- webhook receiver frameworks

## Canonical Flow

1. Initialize the SDK with Integrations client credentials.
2. Create or fetch creators as needed for operational setup.
3. Create links.
4. Issue an IIT.
5. Lock a PIT.
6. Record the paid order with a stable external order id.
7. Record later refunds with stable external refund ids.

## Preferred Initialization

```php
<?php

use Qredex\Config\QredexConfig;
use Qredex\Qredex;
use Qredex\Auth\QredexScope;

$qredex = Qredex::init(QredexConfig::fromEnvironment(
    scope: [
        QredexScope::LINKS_WRITE,
        QredexScope::INTENTS_WRITE,
        QredexScope::ORDERS_WRITE,
    ],
    timeoutMs: 15_000,
));
```

## Canonical Example

```php
<?php

use Qredex\Config\QredexConfig;
use Qredex\Qredex;
use Qredex\Auth\QredexScope;
use Qredex\Request\CreateLinkRequest;
use Qredex\Request\IssueInfluenceIntentTokenRequest;
use Qredex\Request\LockPurchaseIntentRequest;
use Qredex\Request\RecordPaidOrderRequest;
use Qredex\Request\RecordRefundRequest;

$qredex = Qredex::init(QredexConfig::fromEnvironment(
    scope: [
        QredexScope::LINKS_WRITE,
        QredexScope::INTENTS_WRITE,
        QredexScope::ORDERS_WRITE,
    ],
));

$link = $qredex->links()->create(new CreateLinkRequest(
    storeId: '61abc354-dd8d-4a23-be02-ece77b1b4da6',
    creatorId: '16fca3f2-b346-4f98-8e52-0895aac61c5b',
    linkName: 'spring-launch',
    destinationPath: '/products/spring-launch',
    attributionWindowDays: 30,
));

$iit = $qredex->intents()->issueInfluenceIntentToken(new IssueInfluenceIntentTokenRequest(
    linkId: $link->id,
    landingPath: '/products/spring-launch',
));

$pit = $qredex->intents()->lockPurchaseIntent(new LockPurchaseIntentRequest(
    token: $iit->token,
    source: 'backend-cart',
));

$order = $qredex->orders()->recordPaidOrder(new RecordPaidOrderRequest(
    storeId: $link->storeId,
    externalOrderId: 'order-100045',
    currency: 'USD',
    orderNumber: '100045',
    totalPrice: 110.00,
    purchaseIntentToken: $pit->token,
));

$qredex->refunds()->recordRefund(new RecordRefundRequest(
    storeId: $link->storeId,
    externalOrderId: 'order-100045',
    externalRefundId: 'refund-100045-1',
    refundTotal: 25.00,
));
```

## Operational Notes

- Tokens are fetched automatically and reused until close to expiry.
- The typed request objects mirror the canonical write payloads and are the safest default.
- `QredexScope` is the preferred programmatic scope input. Raw scope strings remain for environment/bootstrap compatibility.
- Legacy array payloads still work, but the typed request path is the preferred public API.
- `Retry-After` is honored for opt-in read retries.
- Client-side correlation ids can be emitted via `requestIdFactory` and optionally attached to a configured request header.
- Keep `store_id`, creator identity, and external order/refund ids stable to preserve determinism and idempotency.
