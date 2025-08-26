<?php

namespace SkyHackeR\MultiAuth\Traits;

/**
 * Trait to help logout a specific guard.
 */
trait LogsoutGuard
{
    public function logoutGuard($guard)
    {
        auth()->guard($guard)->logout();
    }
}
