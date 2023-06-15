<?php

namespace Ycs77\LaravelRecoverSession;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class RecoverSessionServiceProvider extends ServiceProvider
{
    /**
     * Register service for package.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/recover-session.php', 'recover-session');

        $this->app->singleton(RecoverSession::class, function (Application $app) {
            $cacheDriver = $app->make('config')->get('recover-session.cache_driver');

            return new RecoverSession(
                $app->make('config'),
                $app->make('cache')->store($cacheDriver),
                $app->make('session.store'),
                $app->make('encrypter'),
                $app->make(UserSource::class)
            );
        });
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
