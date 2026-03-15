<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Qredex\Qredex;

$qredex = Qredex::bootstrap();

$refund = $qredex->refunds()->recordRefund([
    'store_id' => '61abc354-dd8d-4a23-be02-ece77b1b4da6',
    'external_order_id' => 'order-100045',
    'external_refund_id' => 'refund-100045-1',
    'refund_total' => 25.00,
]);

var_dump($refund);
