<?php

declare(strict_types=1);

namespace Qredex\Auth;

use Closure;
use Qredex\Error\ConfigurationError;

final readonly class AccessTokenAuthentication implements QredexAuthentication
{
    public function __construct(public string|Closure $accessToken)
    {
        if (is_string($this->accessToken) && trim($this->accessToken) === '') {
            throw new ConfigurationError('Qredex access token must be a non-empty string.', errorCode: 'sdk_configuration_error');
        }
    }
}
