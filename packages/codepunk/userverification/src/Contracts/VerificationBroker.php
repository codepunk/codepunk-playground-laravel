<?php

namespace Codepunk\UserVerification\Contracts;

use Closure;

interface VerificationBroker
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
     * Send a verification link to a user.
     *
     * @param  array  $credentials
     * @return string
     */
    public function sendVerificationLink(array $credentials);

    /**
     * Verifies the user account for the given token.
     *
     * @param  string   $token
     * @param  Closure  $callback
     * @return mixed
     */
    public function verify($token, Closure $callback);
}