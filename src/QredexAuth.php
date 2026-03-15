<?php

declare(strict_types=1);

namespace Qredex;

use Qredex\Internal\TokenProvider;
use Qredex\Model\OAuthToken;

final readonly class QredexAuth
{
    public function __construct(private TokenProvider $tokenProvider)
    {
    }

    public function issueToken(string|array|null $scope = null): OAuthToken
    {
        return $this->tokenProvider->issueToken($scope);
    }

    public function clearTokenCache(): void
    {
        $this->tokenProvider->clear();
    }
}
