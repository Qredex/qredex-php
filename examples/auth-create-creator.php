<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Qredex\Qredex;

$qredex = Qredex::bootstrap();

$token = $qredex->auth()->issueToken();

$creator = $qredex->creators()->create([
    'handle' => 'amelia-rose',
    'display_name' => 'Amelia Rose',
    'email' => 'ops@example.com',
]);

var_dump($token, $creator);
