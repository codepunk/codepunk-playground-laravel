<?php

namespace Codepunk\UserVerification\Support\Facades;

use Illuminate\Support\Facades\Facade;

class UserVerification extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        return 'verification';
    }
}