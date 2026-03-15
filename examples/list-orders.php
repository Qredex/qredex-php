<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Qredex\Qredex;

$qredex = Qredex::bootstrap();

$orders = $qredex->orders()->list([
    'page' => 0,
    'size' => 25,
]);

var_dump($orders);
