<?php

namespace Codepunk\UserVerification;

use Codepunk\UserVerification\Contracts\CanVerifyUser as CanVerifyUserContract;

interface TokenRepositoryInterface
{
    /**
     * Create a new token.
     *
     * @param  \Codepunk\UserVerification\Contracts\CanVerifyUser  $user
     * @return string
     */
    public function create(CanVerifyUserContract $user);

    /**
     * Determine if a token record exists and is valid.
     *
     * @param  \Codepunk\UserVerification\Contracts\CanVerifyUser  $user
     * @param  string  $token
     * @return bool
     */
    public function exists(CanVerifyUserContract $user, $token);

    /**
     * Delete a token record.
     *
     * @param  string  $token
     * @return void
     */
    public function delete($token);

    /**
     * Delete expired tokens.
     *
     * @return void
     */
    public function deleteExpired();

    /**
     * Gets the user id matching the given token.
     * TODO Do I need this? This isn't in auth version.
     *
     * @param $token
     * @return string
     */
    public function getUserIdByToken($token);
}