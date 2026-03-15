<?php

declare(strict_types=1);

namespace Qredex\Http;

interface HttpTransportInterface
{
    public function send(TransportRequest $request): TransportResponse;
}
