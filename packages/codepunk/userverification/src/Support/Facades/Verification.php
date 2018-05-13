<?php

namespace Codepunk\UserVerification\Support\Facades;

use Illuminate\Support\Facades\Facade;

class Verification extends Facade
{
    /**
     * Constant representing a successfully sent reminder.
     *
     * @var string
     */
    const VERIFICATION_LINK_SENT = 'verification.sent';

    /**
     * Constant representing a successfully verified user.
     *
     * @var string
     */
    const VERIFIED = 'verification.verified';

    /**
     * Constant representing the user not found response.
     *
     * @var string
     */
    const INVALID_USER = 'verification.user';

    /**
     * Constant representing an invalid token.
     *
     * @var string
     */
    const INVALID_TOKEN = 'verification.token';

    /**
     * Constant representing an unverified user.
     *
     * @var string
     */
    const UNVERIFIED = 'verification.unverified';

    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        return 'user.verification';
    }
}
