<?php

namespace Ycs77\LaravelRecoverSession;

use Illuminate\Support\ServiceProvider;

class RecoverSessionServiceProvider extends ServiceProvider
{
    /**
     * Register service for package.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/recover-session.php', 'recover-session');
    }

    /**
     * Bootstrap service for package.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/recover-session.php' => config_path('recover-session.php'),
        ], 'recover-session-config');
    }
}
