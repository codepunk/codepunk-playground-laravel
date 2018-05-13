<?php

namespace Codepunk\UserVerification;

use Closure;
use Codepunk\UserVerification\Contracts\CanVerifyUser as CanVerifyUserContract;
use Codepunk\UserVerification\Contracts\VerificationBroker as VerificationBrokerContract;
use Illuminate\Contracts\Auth\UserProvider;
use UnexpectedValueException;

class VerificationBroker implements VerificationBrokerContract
{
    /**
     * The verification token repository.
     *
     * @var \Codepunk\UserVerification\TokenRepositoryInterface
     */
    protected $tokens;

    /**
     * The user provider implementation.
     *
     * @var \Illuminate\Contracts\Auth\UserProvider
     */
    protected $users;

    /**
     * Create a new password broker instance.
     *
     * @param  \Codepunk\UserVerification\TokenRepositoryInterface $tokens
     * @param  \Illuminate\Contracts\Auth\UserProvider  $users
     */
    public function __construct(TokenRepositoryInterface $tokens,
                                UserProvider $users)
    {
        $this->users = $users;
        $this->tokens = $tokens;
    }

    /**
     * Send a verification link to a user.
     *
     * @param  array $credentials
     * @return string
     */
    public function sendVerificationLink(array $credentials)
    {
        // First we will check to see if we found a user at the given credentials and
        // if we did not we will redirect back to this current URI with a piece of
        // "flash" data in the session to indicate to the developers the errors.
        $user = $this->getUserByCredentials($credentials);
        if (is_null($user)) {
            return static::INVALID_USER;
        }
        // Once we have the verification token, we are ready to send the message out to
        // this user with a link to verify their account. We will then redirect back
        // to the current URI having nothing in the session indicating any errors.
        $user->sendVerificationNotification(
            $this->tokens->create($user)
        );
        return static::USER_VERIFICATION_LINK_SENT;
    }

    /**
     * Verifies the user account for the given token.
     *
     * @param  string  $token
     * @param  Closure $callback
     * @return mixed
     */
    public function verify($token, Closure $callback)
    {
        // If the responses from the validate method is not a user instance, we will
        // assume that it is a redirect and simply return it from this method and
        // the user is properly redirected having an error message on the post.
        $user = $this->validateVerify($token);
        if (!($user instanceof CanVerifyUserContract)) {
            return $user;
        }
        // Once we have called this callback, we will remove this token row from the
        // table and return the response from this callback so the user gets sent
        // to the destination given by the developers from the callback return.
        $callback($user);
        $this->tokens->delete($token);
        return static::USER_VERIFIED;
    }

    /**
     * Validate a verification for the given credentials.
     *
     * @param  string $token
     * @return \Codepunk\UserVerification\Contracts\CanVerifyUser|string TODO
     */
    protected function validateVerify($token)
    {
        if (is_null($user = $this->getUserByToken($token))) {
            return static::INVALID_TOKEN;
        }
        // This may seem redundant but 'exists' also checks for expired token
        if (!($this->tokens->exists($user, $token))) {
            return static::INVALID_TOKEN;
        }
        return $user;
    }

    /**
     * Get the user for the given credentials.
     *
     * @param  array  $credentials
     * @return \Codepunk\UserVerification\Contracts\CanVerifyUser TODO
     *
     * @throws UnexpectedValueException
     */
    public function getUserByCredentials(array $credentials)
    {
        $user = $this->users->retrieveByCredentials($credentials);
        if ($user && !($user instanceof CanVerifyUserContract)) {
            throw new UnexpectedValueException('User must implement CanVerifyUser interface.');
        }
        return $user;
    }

    /**
     * Get the user for the given validation token.
     * @param  string $token
     * @return \Codepunk\UserVerification\Contracts\CanVerifyUser TODO
     *
     * @throws UnexpectedValueException
     */
    public function getUserByToken($token) {
        $user = $this->users->retrieveById(
            $this->tokens->getUserIdByToken($token)
        );
        if ($user && !($user instanceof CanVerifyUserContract)) {
            throw new UnexpectedValueException('User must implement CanVerifyUser interface.');
        }
        return $user;
    }

    /**
     * Create a new password reset token for the given user.
     *
     * @param  \Codepunk\UserVerification\Contracts\CanVerifyUser $user
     * @return string
     */
    public function createToken(CanVerifyUserContract $user)
    {
        return $this->tokens->create($user);
    }

    /**
     * Delete the given password reset token.
     *
     * @param  string  $token
     * @return void
     */
    public function deleteToken($token)
    {
        $this->tokens->delete($token);
    }

    /**
     * Validate the given verification token.
     *
     * @param  \Codepunk\UserVerification\Contracts\CanVerifyUser $user
     * @param  string $token
     * @return bool
     */
    public function tokenExists(CanVerifyUserContract $user, $token)
    {
        return $this->tokens->exists($user, $token);
    }

    /**
     * Get the verification token repository implementation.
     *
     * @return \Codepunk\UserVerification\TokenRepositoryInterface
     */
    public function getRepository()
    {
        return $this->tokens;
    }
}
