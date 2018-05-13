<?php

namespace Codepunk\UserVerification;

use Codepunk\UserVerification\Contracts\VerificationBrokerFactory as FactoryContract;
use Illuminate\Support\Str;
use InvalidArgumentException;

class VerificationBrokerManager implements FactoryContract
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * The array of created "drivers".
     *
     * @var array
     */
    protected $brokers = [];

    /**
     * Create a new PasswordBroker manager instance.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Attempt to get the broker from the local cache.
     *
     * @param  string  $name
     * @return \Codepunk\UserVerification\Contracts\VerificationBroker
     */
    public function broker($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();
        return isset($this->brokers[$name])
            ? $this->brokers[$name]
            : $this->brokers[$name] = $this->resolve($name);
    }
    /**
     * Resolve the given broker.
     *
     * @param  string  $name
     * @return \Codepunk\UserVerification\Contracts\VerificationBroker
     *
     * @throws \InvalidArgumentException
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);
        if (is_null($config)) {
            throw new InvalidArgumentException("Account verifier [{$name}] is not defined.");
        }
        // The verification broker uses a token repository to validate tokens and send
        // user verification e-mails, and validates that verification process as an
        // aggregate service of sorts that offers an interface for verifications.
        return new VerificationBroker(
            $this->createTokenRepository($config),
            $this->app['auth']->createUserProvider($config['provider'])
        );
    }

    /**
     * Create a token repository instance based on the given configuration.
     *
     * @param  array  $config
     * @return \Codepunk\UserVerification\TokenRepositoryInterface
     */
    protected function createTokenRepository(array $config)
    {
        $key = $this->app['config']['app.key'];
        if (Str::startsWith($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }
        $connection = isset($config['connection']) ? $config['connection'] : null;
        return new DatabaseTokenRepository(
            $this->app['db']->connection($connection),
            $config['table'],
            $key,
            $config['expire']
        );
    }

    /**
     * Get the verification broker configuration.
     *
     * @param  string  $name
     * @return array
     */
    protected function getConfig($name)
    {
        return $this->app['config']["auth.verifications.{$name}"];
    }

    /**
     * Get the default verification broker name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['auth.defaults.verifications'];
    }

    /**
     * Set the default verification broker name.
     *
     * @param  string  $name
     * @return void
     */
    public function setDefaultDriver($name)
    {
        $this->app['config']['auth.defaults.verifications'] = $name;
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->broker()->{$method}(...$parameters);
    }
}
