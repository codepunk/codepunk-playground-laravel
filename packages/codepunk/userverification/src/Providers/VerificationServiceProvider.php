<?php

namespace Codepunk\UserVerification\Providers;

use Codepunk\UserVerification\Support\Facades\Verification;
use Codepunk\UserVerification\VerificationBrokerManager;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class VerificationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/verification.php', 'verifications'
        );

        $this->loadMigrationsFrom(
            __DIR__.'/../../database/migrations'
        );

        $this->loadRoutesFrom(
            __DIR__ . '/../../routes/routes.php'
        );
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerVerificationBroker();

        App::bind('user.verification', function()
        {
            return new Verification;
        });
    }

    /**
     * Register the verification broker instance.
     *
     * @return void
     */
    protected function registerVerificationBroker()
    {
        $this->app->singleton('user.verification', function ($app) {
            return new VerificationBrokerManager($app);
        });

        $this->app->bind('user.verification.broker', function ($app) {
            return $app->make('user.verification')->broker();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['user.verification', 'user.verification.broker'];
    }
}
