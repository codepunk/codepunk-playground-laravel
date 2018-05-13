<?php

namespace Codepunk\UserVerification\Contracts;

interface CanVerifyUser
{
    /**
     * Get the user id for processing verification requests.
     *
     * @return string
     */
    public function getIdForVerification();

    /**
     * Send the verification notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendVerificationNotification($token);
}