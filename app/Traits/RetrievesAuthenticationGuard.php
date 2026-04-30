<?php

declare(strict_types=1);

namespace App\Traits;

use LogicException;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

trait RetrievesAuthenticationGuard
{
    /**
     * Retrieves the authentication guard
     */
    private function getGuard(): JWTGuard
    {
        if (! ($guard = auth('api')) instanceof JWTGuard) {
            throw new LogicException('The authentication guard must be a JWT authentication guard.');
        }

        return $guard;
    }
}
