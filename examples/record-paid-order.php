<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Qredex\Qredex;

$qredex = Qredex::bootstrap();

$order = $qredex->orders()->recordPaidOrder([
    'store_id' => '61abc354-dd8d-4a23-be02-ece77b1b4da6',
    'external_order_id' => 'order-100045',
    'order_number' => '100045',
    'currency' => 'USD',
    'total_price' => 110.00,
    'purchase_intent_token' => 'eyJhbGciOiJIUzI1NiJ9.example',
]);

var_dump($order);
