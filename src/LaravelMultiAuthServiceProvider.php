<?php

namespace SkyHackeR\MultiAuth;

use Illuminate\Support\ServiceProvider;
use SkyHackeR\MultiAuth\Commands\InstallMultiAuthCommand;

/**
 * Forked and upgraded for Laravel 10 by SkyHackeR
 * Original author: Al Amin Firdows
 */
class LaravelMultiAuthServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Merge package config with app config
        $this->mergeConfigFrom(__DIR__ . '/../config/multiauth.php', 'multiauth');
    }

    public function boot(){
        if ($this->app->runningInConsole()) {
            // Publish config
            $this->publishes([
                __DIR__ . '/../config/multiauth.php' => config_path('multiauth.php'),
            ], 'multi-auth-config');

            // Publish views
            $this->publishes([
                __DIR__ . '/stubs/views' => resource_path('views/vendor/multiauth'),
            ], 'multi-auth-views');

            // Publish migrations
            $this->publishes([
                __DIR__ . '/stubs/migrations' => database_path('migrations'),
            ], 'multi-auth-migrations');

            // Publish models
            $this->publishes([
                __DIR__ . '/stubs/models' => app_path('Models'),
            ], 'multi-auth-models');

            // Publish notifications
            $this->publishes([
                __DIR__ . '/stubs/notifications' => app_path('Notifications'),
            ], 'multi-auth-notifications');

            // Publish routes
            $this->publishes([
                __DIR__ . '/stubs/routes' => base_path('routes'),
            ], 'multi-auth-routes');

            // Publish controllers
            $this->publishes([
                __DIR__ . '/stubs/controllers' => app_path('Http/Controllers'),
            ], 'multi-auth-controllers');

            // Register command
            $this->commands([
                InstallMultiAuthCommand::class,
            ]);
        }
    }

}
