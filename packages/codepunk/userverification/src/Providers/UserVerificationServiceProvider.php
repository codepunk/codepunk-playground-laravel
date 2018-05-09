<?php

namespace Codepunk\UserVerification\Providers;

use Codepunk\UserVerification\Support\Facades\UserVerification;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class UserVerificationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/userverification.php', 'userverification'
        );

        $this->loadMigrationsFrom(
            __DIR__.'/../../database/migrations'
        );
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        App::bind('verification', function()
        {
            return new UserVerification;
        });
    }
}
