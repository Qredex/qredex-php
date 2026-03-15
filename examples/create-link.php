<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Qredex\Qredex;

$qredex = Qredex::bootstrap();

$link = $qredex->links()->create([
    'store_id' => '61abc354-dd8d-4a23-be02-ece77b1b4da6',
    'creator_id' => '16fca3f2-b346-4f98-8e52-0895aac61c5b',
    'link_name' => 'spring-launch',
    'destination_path' => '/products/spring-launch',
    'attribution_window_days' => 30,
]);

var_dump($link);
