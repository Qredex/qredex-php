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

# Qredex PHP SDK

[![CI](https://github.com/Qredex/qredex-php/actions/workflows/ci.yml/badge.svg)](https://github.com/Qredex/qredex-php/actions/workflows/ci.yml)
[![License](https://img.shields.io/badge/license-Apache--2.0-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/php-%5E8.2-8892BF.svg)](https://www.php.net/)

Canonical PHP server SDK for the Qredex Integrations API.

This package is for machine-to-machine integrations only. It is designed to make the canonical backend flow easy and safe:

`create link -> issue IIT -> lock PIT -> record paid order -> record refund`

It does not include Merchant API endpoints, browser/runtime logic, Shopify embedded flows, or webhook receivers.

## Installation

```bash
composer require qredex/php
```

## Requirements

- PHP 8.2 or newer
- Composer 2
- Machine-to-machine Qredex Integrations API credentials

## Quick Start

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

$refund = $qredex->refunds()->recordRefund(new RecordRefundRequest(
    storeId: $link->storeId,
    externalOrderId: 'order-100045',
    externalRefundId: 'refund-100045-1',
    refundTotal: 25.00,
));
```

## Initialization

Preferred typed initialization:

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
    requestIdHeader: 'x-client-request-id',
    requestIdFactory: static fn (): string => bin2hex(random_bytes(16)),
));
```

Required environment variables:

- `QREDEX_CLIENT_ID`
- `QREDEX_CLIENT_SECRET`

Optional environment variables:

- `QREDEX_SCOPE`
- `QREDEX_ENVIRONMENT`
- `QREDEX_BASE_URL`
- `QREDEX_TIMEOUT_MS`

Programmatic configuration should prefer `QredexScope` values over raw scope strings.

`Qredex::bootstrap()` remains available as the convenience path for environment-only initialization, while `QredexConfig::fromEnvironment()` is the recommended typed option for any customization.

## Stability

- The canonical typed write flow is the primary stable public surface.
- Legacy array payloads remain supported for concise adoption.
- The public surface is intentionally limited to Integrations API resource operations and automatic client-credentials auth.

## Public API

Stable canonical write surface:

```php
$qredex->creators()->create(...);
$qredex->links()->create(...);
$qredex->intents()->issueInfluenceIntentToken(...);
$qredex->intents()->lockPurchaseIntent(...);
$qredex->orders()->recordPaidOrder(...);
$qredex->refunds()->recordRefund(...);
```

Operational read surface:

```php
$qredex->creators()->get($creatorId);
$qredex->creators()->list([...]);
$qredex->links()->get($linkId);
$qredex->links()->list([...]);
$qredex->links()->getStats($linkId);
$qredex->orders()->list([...]);
$qredex->orders()->getDetails($orderAttributionId);
```

## Errors

The SDK separates local validation, API validation, and protocol/response failures:

- `Qredex\Error\RequestValidationError`
- `Qredex\Error\ApiValidationError`
- `Qredex\Error\ResponseDecodingError`
- `Qredex\Error\AuthenticationError`
- `Qredex\Error\AuthorizationError`
- `Qredex\Error\NotFoundError`
- `Qredex\Error\ConflictError`
- `Qredex\Error\RateLimitError`
- `Qredex\Error\ApiError`
- `Qredex\Error\NetworkError`
- `Qredex\Error\ConfigurationError`

## Operational Defaults

- OAuth tokens are issued automatically and cached until close to expiry.
- Writes are never retried automatically.
- Read retries are opt-in and honor `Retry-After` when provided.
- Client-side correlation ids can be emitted and optionally attached to outgoing headers.
- The SDK does not log client secrets, bearer tokens, IITs, or PITs by default.

## Releases

- Releases are semantic-version Git tags such as `v0.2.0`.
- GitHub Actions validates tagged releases, creates a GitHub Release, and can optionally notify Packagist through `PACKAGIST_WEBHOOK_URL`.
- Composer consumers should install tagged releases from Packagist or the GitHub repository source configured in Composer.

## Docs

- [Integration Guide](docs/INTEGRATION_GUIDE.md)
- [API Reference](docs/API_REFERENCE.md)
- [Errors](docs/ERRORS.md)
- [Contributing](CONTRIBUTING.md)
- [Security](SECURITY.md)
- [Changelog](CHANGELOG.md)

## Examples

- [canonical-flow.php](examples/canonical-flow.php)
- [create-creator.php](examples/create-creator.php)
- [create-link.php](examples/create-link.php)
- [issue-iit.php](examples/issue-iit.php)
- [lock-pit.php](examples/lock-pit.php)
- [record-paid-order.php](examples/record-paid-order.php)
- [record-refund.php](examples/record-refund.php)
- [list-orders.php](examples/list-orders.php)
- [get-order-details.php](examples/get-order-details.php)

## Testing

- `composer test` runs unit and mocked transport tests only
- `composer test:live` runs the opt-in live integration suite

Live tests are skipped unless `QREDEX_LIVE_ENABLED=1` and the required `QREDEX_LIVE_*` variables are set.

Start from [`.env.live.example`](.env.live.example) when wiring live test credentials.

## Release Notes

- [Releasing](docs/RELEASING.md)
