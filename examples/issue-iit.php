<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Qredex\Qredex;

$qredex = Qredex::bootstrap();

$iit = $qredex->intents()->issueInfluenceIntentToken([
    'link_id' => '2a6ce204-5651-4f52-b135-e42ff0f8d1b5',
    'landing_path' => '/products/spring-launch',
    'referrer' => 'https://creator.example/post/123',
]);

var_dump($iit);
