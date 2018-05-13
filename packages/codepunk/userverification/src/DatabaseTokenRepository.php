<?php

namespace Codepunk\UserVerification;

use Carbon\Carbon;
use Codepunk\UserVerification\Contracts\CanVerifyUser as CanVerifyUserContract;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Str;

class DatabaseTokenRepository implements TokenRepositoryInterface
{
    /**
     * The database connection instance.
     *
     * @var \Illuminate\Database\ConnectionInterface
     */
    protected $connection;

    /**
     * The token database table.
     *
     * @var string
     */
    protected $table;

    /**
     * The hashing key.
     *
     * @var string
     */
    protected $hashKey;

    /**
     * The number of seconds a token should last.
     *
     * @var int
     */
    protected $expires;

    /**
     * Create a new token repository instance.
     *
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  string  $table
     * @param  string  $hashKey
     * @param  int  $expires
     */
    public function __construct(ConnectionInterface $connection, $table, $hashKey, $expires = 60)
    {
        $this->table = $table;
        $this->hashKey = $hashKey;
        $this->expires = $expires * 60;
        $this->connection = $connection;
    }

    /**
     * Create a new token.
     *
     * @param  \Codepunk\UserVerification\Contracts\CanVerifyUser $user
     * @return string
     */
    public function create(CanVerifyUserContract $user)
    {
        $userId = $user->getIdForVerification();
        $this->deleteExisting($user);
        // We will create a new, random token for the user so that we can e-mail them
        // a safe link to the password reset form. Then we will insert a record in
        // the database so that we can verify the token within the actual reset.
        $token = $this->createNewToken();
        $this->getTable()->insert($this->getPayload($userId, $token));
        return $token;
    }

    /**
     * Delete all existing reset tokens from the database.
     *
     * @param  \Codepunk\UserVerification\Contracts\CanVerifyUser  $user
     * @return int
     */
    protected function deleteExisting(CanVerifyUserContract $user)
    {
        return $this->getTable()->where('user_id', $user->getIdForVerification())->delete();
    }

    /**
     * Build the record payload for the table.
     *
     * @param  integer  $userId
     * @param  string   $token
     * @return array
     */
    protected function getPayload($userId, $token)
    {
        return [
            'user_id' => $userId,
            'token' => $token,
            'created_at' => new Carbon
        ];
    }

    /**
     * Determine if a token record exists and is valid.
     *
     * @param  \Codepunk\UserVerification\Contracts\CanVerifyUser $user
     * @param  string $token
     * @return bool
     */
    public function exists(CanVerifyUserContract $user, $token)
    {
        $userId = $user->getIdForVerification();
        $token = (array) $this->getTable()
            ->where('user_id', $userId)
            ->where('token', $token)
            ->first();
        return $token && ! $this->tokenExpired($token);
    }

    /**
     * Determine if the token has expired.
     *
     * @param  array  $token
     * @return bool
     */
    protected function tokenExpired($token)
    {
        $expiresAt = Carbon::parse($token['created_at'])->addSeconds($this->expires);
        return $expiresAt->isPast();
    }

    /**
     * Delete a token record.
     *
     * @param  string $token
     * @return void
     */
    public function delete($token)
    {
        $this->getTable()->where('token', $token)->delete();
    }

    /**
     * Delete expired tokens.
     *
     * @return void
     */
    public function deleteExpired()
    {
        $expiredAt = Carbon::now()->subSeconds($this->expires);
        $this->getTable()->where('created_at', '<', $expiredAt)->delete();
    }

    /**
     * Create a new token for the user.
     *
     * @return string
     */
    public function createNewToken()
    {
        return hash_hmac('sha256', Str::random(40), $this->hashKey);
    }

    /**
     * Begin a new database query against the table.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getTable()
    {
        return $this->connection->table($this->table);
    }

    /**
     * Get the database connection instance.
     *
     * @return \Illuminate\Database\ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Gets the user id matching the given token.
     *
     * @param $token
     * @return string
     */
    public function getUserIdByToken($token)
    {
        $token = (array) $this->getTable()
            ->where('token', $token)
            ->first();
        return isset($token['user_id']) ? $token['user_id'] : null;
    }
}