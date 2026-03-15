<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Qredex\Qredex;

$qredex = Qredex::bootstrap();

$order = $qredex->orders()->getDetails('53f87935-fc8f-4ff6-91a8-51fc9e7653a7');

var_dump($order);
