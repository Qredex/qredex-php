# Errors

Qredex PHP SDK normalizes API and transport failures into a small typed hierarchy.

## Error Types

- `Qredex\Error\ConfigurationError`
  Raised before a request is made when local SDK configuration is invalid.
- `Qredex\Error\ValidationError`
  Raised for local request validation failures and API `400` / `422` responses.
- `Qredex\Error\AuthenticationError`
  Raised for API `401` responses.
- `Qredex\Error\AuthorizationError`
  Raised for API `403` responses.
- `Qredex\Error\ConflictError`
  Raised for API `409` responses.
- `Qredex\Error\RateLimitError`
  Raised for API `429` responses.
- `Qredex\Error\ApiError`
  Raised for any other non-success API response.
- `Qredex\Error\NetworkError`
  Raised when no valid response is received.

## Preserved Metadata

Every `QredexError` preserves these fields when available:

- `status`
- `errorCode`
- `requestId`
- `traceId`
- `responseBody`
- `responseText`
- `retryAfterSeconds`

## Example

```php
<?php

use Qredex\Error\ConflictError;
use Qredex\Error\QredexError;

try {
    $qredex->orders()->recordPaidOrder([
        'store_id' => '61abc354-dd8d-4a23-be02-ece77b1b4da6',
        'external_order_id' => 'order-100045',
        'currency' => 'USD',
    ]);
} catch (ConflictError $error) {
    echo $error->status;
    echo $error->errorCode;
    echo $error->requestId;
} catch (QredexError $error) {
    echo $error->getMessage();
}
```

## Notes

- `INGESTED` and `IDEMPOTENT` are success concepts in the Qredex ingestion model, but the current Integrations API responses do not expose a separate ingestion decision field in successful response bodies.
- Conflict and policy outcomes are surfaced through `409` responses and become `ConflictError`.
- Raw response text is preserved for debugging, but the SDK itself does not log secrets or raw tokens.
