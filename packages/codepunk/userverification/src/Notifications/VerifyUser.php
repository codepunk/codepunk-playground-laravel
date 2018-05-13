<?php

namespace Codepunk\UserVerification\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

class VerifyUser
{
    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

    /**
     * Create a notification instance.
     *
     * @param  string  $token
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's channels.
     *
     * @param  mixed  $notifiable
     * @return array|string
     */
    public function via(/** @noinspection PhpUnusedParameterInspection */
        $notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(/** @noinspection PhpUnusedParameterInspection */ $notifiable)
    {
        return (new MailMessage())
            ->subject(trans('userverification.email.subject'))
            ->line(trans('userverification.email.reason'))
            ->action(trans('userverification.email.action'), url('register/verify', $this->token))
            ->line(trans('userverification.email.disclaimer'));
    }
}
