<?php

namespace Codepunk\UserVerification;

use Codepunk\UserVerification\Notifications\VerifyUser as VerifyUserNotification;

trait CanVerifyUser
{
    /**
     * Get the user id for processing verification requests.
     *
     * @return string
     */
    public function getIdForVerification() {
        /** @noinspection PhpUndefinedFieldInspection */
        return $this->id;
    }

    /**
     * Send the verification notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendVerificationNotification($token) {
        $this->notify(new VerifyUserNotification($token));
    }
}
