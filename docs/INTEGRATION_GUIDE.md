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

1. Authenticate with Direct API client credentials.
2. Create or fetch creators.
3. Create or fetch links.
4. Issue an IIT for backend click flows when needed.
5. Lock a PIT for the canonical machine purchase flow.
6. Record the paid order.
7. Record later refunds with stable external refund ids.

## Bootstrap

```php
<?php

use Qredex\Qredex;

$qredex = Qredex::bootstrap();
```

## Explicit Config

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
        scope: 'direct:creators:write direct:links:write direct:intents:write direct:orders:write',
    ),
    environment: QredexEnvironment::PRODUCTION,
));
```

## Creators

```php
$creator = $qredex->creators()->create([
    'handle' => 'amelia-rose',
    'display_name' => 'Amelia Rose',
    'email' => 'ops@example.com',
]);
```

## Links

```php
$link = $qredex->links()->create([
    'store_id' => '61abc354-dd8d-4a23-be02-ece77b1b4da6',
    'creator_id' => $creator->id,
    'link_name' => 'spring-launch',
    'destination_path' => '/products/spring-launch',
    'attribution_window_days' => 30,
]);

$stats = $qredex->links()->getStats($link->id);
```

## IIT and PIT

```php
$iit = $qredex->intents()->issueInfluenceIntentToken([
    'link_id' => $link->id,
    'landing_path' => '/products/spring-launch',
]);

$pit = $qredex->intents()->lockPurchaseIntent([
    'token' => $iit->token,
    'source' => 'backend-cart',
]);
```

Additional supported read methods:

```php
$qredex->intents()->get($pit->token);
$qredex->intents()->getByTokenId($pit->tokenId);
$qredex->intents()->getByInfluenceIntentToken($iit->token);
$qredex->intents()->latestUnlocked(24);
```

## Orders

```php
$order = $qredex->orders()->recordPaidOrder([
    'store_id' => '61abc354-dd8d-4a23-be02-ece77b1b4da6',
    'external_order_id' => 'order-100045',
    'order_number' => '100045',
    'currency' => 'USD',
    'total_price' => 110.00,
    'purchase_intent_token' => $pit->token,
]);

$orders = $qredex->orders()->list(['page' => 0, 'size' => 25]);
$details = $qredex->orders()->getDetails($order->id);
```

## Refunds

```php
$qredex->refunds()->recordRefund([
    'store_id' => '61abc354-dd8d-4a23-be02-ece77b1b4da6',
    'external_order_id' => 'order-100045',
    'external_refund_id' => 'refund-100045-1',
    'refund_total' => 25.00,
]);
```

## Operational Notes

- Tokens are fetched automatically and reused until close to expiry.
- The SDK never logs client secrets or raw bearer tokens by default.
- Writes are never retried automatically.
- Read retries are opt-in through `QredexConfig`.
- Keep `store_id`, creator identity, and external order/refund ids stable to preserve determinism and idempotency.
