<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Qredex\Qredex;

$qredex = Qredex::bootstrap();

$pit = $qredex->intents()->lockPurchaseIntent([
    'token' => 'eyJhbGciOiJIUzI1NiJ9.example',
    'source' => 'backend-cart',
]);

var_dump($pit);
