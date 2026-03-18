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

# Errors

Qredex PHP SDK normalizes failures into a small typed hierarchy with clearer boundaries.

## Validation Boundaries

- `Qredex\Error\RequestValidationError`
  Raised before a request is sent when the local SDK input is invalid.
- `Qredex\Error\ApiValidationError`
  Raised for API `400` and `422` validation responses.
- `Qredex\Error\ResponseDecodingError`
  Raised when the API response shape is invalid or cannot be decoded into the SDK model.

## Auth, Policy, and Transport

- `Qredex\Error\AuthenticationError`
  Raised for API `401` responses.
- `Qredex\Error\AuthorizationError`
  Raised for API `403` responses.
- `Qredex\Error\NotFoundError`
  Raised for API `404` responses.
- `Qredex\Error\ConflictError`
  Raised for API `409` responses.
- `Qredex\Error\RateLimitError`
  Raised for API `429` responses.
- `Qredex\Error\ApiError`
  Raised for any other non-success API response.
- `Qredex\Error\NetworkError`
  Raised when no valid response is received.
- `Qredex\Error\ConfigurationError`
  Raised before requests are made when local SDK configuration is invalid.

## Preserved Metadata

Every `Qredex\Error\QredexError` preserves these fields when available:

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

use Qredex\Error\ApiValidationError;
use Qredex\Error\QredexError;
use Qredex\Error\RequestValidationError;
use Qredex\Request\RecordPaidOrderRequest;

try {
    $qredex->orders()->recordPaidOrder(new RecordPaidOrderRequest(
        storeId: '61abc354-dd8d-4a23-be02-ece77b1b4da6',
        externalOrderId: 'order-100045',
        currency: 'USD',
    ));
} catch (RequestValidationError $error) {
    echo $error->getMessage();
} catch (ApiValidationError $error) {
    echo $error->status;
    echo $error->errorCode;
    echo $error->requestId;
} catch (QredexError $error) {
    echo $error->getMessage();
}
```
