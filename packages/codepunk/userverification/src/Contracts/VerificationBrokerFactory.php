<?php

namespace Codepunk\UserVerification\Contracts;

interface VerificationBrokerFactory
{
    /**
     * Get a user verification broker instance by name.
     *
     * @param  string|null  $name
     * @return mixed
     */
    public function broker($name = null);
}
