# Qredex PHP SDK

Canonical PHP server SDK for the Qredex Integrations API.
`qredex` for PHP is built for backend systems that need to create creators and links, issue IITs, lock PITs, and record paid orders and refunds without dealing with raw HTTP plumbing.


## Installation

```bash
composer require qredex/php
```

## Quick Start

```php
<?php

use Qredex\Qredex;

$qredex = Qredex::bootstrap();

$creator = $qredex->creators()->create([
    'handle' => 'amelia-rose',
    'display_name' => 'Amelia Rose',
]);

$link = $qredex->links()->create([
    'store_id' => '61abc354-dd8d-4a23-be02-ece77b1b4da6',
    'creator_id' => $creator->id,
    'link_name' => 'spring-launch',
    'destination_path' => '/products/spring-launch',
    'attribution_window_days' => 30,
]);

$iit = $qredex->intents()->issueInfluenceIntentToken([
    'link_id' => $link->id,
    'landing_path' => '/products/spring-launch',
]);

$pit = $qredex->intents()->lockPurchaseIntent([
    'token' => $iit->token,
    'source' => 'backend-cart',
]);

$order = $qredex->orders()->recordPaidOrder([
    'store_id' => $link->storeId,
    'external_order_id' => 'order-100045',
    'order_number' => '100045',
    'currency' => 'USD',
    'total_price' => 110.00,
    'purchase_intent_token' => $pit->token,
]);
```

## Configuration

Environment bootstrap:

```php
$qredex = Qredex::bootstrap();
```

Required environment variables:

- `QREDEX_CLIENT_ID`
- `QREDEX_CLIENT_SECRET`

Optional environment variables:

- `QREDEX_SCOPE`
- `QREDEX_ENVIRONMENT`
- `QREDEX_BASE_URL`
- `QREDEX_TIMEOUT_MS`

Explicit initialization:

```php
<?php

use Qredex\Auth\ClientCredentialsAuthentication;
use Qredex\Config\QredexConfig;
use Qredex\Config\QredexEnvironment;
use Qredex\Qredex;

$qredex = Qredex::init(new QredexConfig(
    auth: new ClientCredentialsAuthentication(
        clientId: 'qdx_client_id',
        clientSecret: 'qdx_client_secret',
        scope: [
            'direct:creators:write',
            'direct:links:write',
            'direct:intents:write',
            'direct:orders:write',
        ],
    ),
    environment: QredexEnvironment::PRODUCTION,
    timeoutMs: 15_000,
));
```

## Public API

```php
$qredex->auth()->issueToken();
$qredex->creators()->create([...]);
$qredex->creators()->get($creatorId);
$qredex->creators()->list([...]);

$qredex->links()->create([...]);
$qredex->links()->get($linkId);
$qredex->links()->list([...]);
$qredex->links()->getStats($linkId);

$qredex->intents()->issueInfluenceIntentToken([...]);
$qredex->intents()->lockPurchaseIntent([...]);
$qredex->intents()->get($pitToken);
$qredex->intents()->getByTokenId($tokenId);
$qredex->intents()->getByInfluenceIntentToken($iitToken);
$qredex->intents()->latestUnlocked();

$qredex->orders()->recordPaidOrder([...]);
$qredex->orders()->list([...]);
$qredex->orders()->getDetails($orderAttributionId);

$qredex->refunds()->recordRefund([...]);
```

## Errors

The SDK raises typed exceptions and preserves:

- HTTP status
- `error_code`
- message
- request id
- trace id
- parsed response body

Main types:

- `Qredex\Error\AuthenticationError`
- `Qredex\Error\AuthorizationError`
- `Qredex\Error\ValidationError`
- `Qredex\Error\ConflictError`
- `Qredex\Error\RateLimitError`
- `Qredex\Error\ApiError`
- `Qredex\Error\NetworkError`
- `Qredex\Error\ConfigurationError`

## Docs

- [Integration Guide](docs/INTEGRATION_GUIDE.md)
- [Errors](docs/ERRORS.md)

## Examples

- [auth-create-creator.php](examples/auth-create-creator.php)
- [create-link.php](examples/create-link.php)
- [issue-iit.php](examples/issue-iit.php)
- [lock-pit.php](examples/lock-pit.php)
- [record-paid-order.php](examples/record-paid-order.php)
- [list-orders.php](examples/list-orders.php)
- [get-order-details.php](examples/get-order-details.php)
- [record-refund.php](examples/record-refund.php)
