<?php

namespace Noorfarooqy\EasyNotifications;

use Illuminate\Support\ServiceProvider;

class EasyNotificationsServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/easy_notifications.php' => config_path('easy_notifications.php'),
        ], 'en-config');

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations/'),
        ], 'en-database');

        $this->loadRoutesFrom(__DIR__. '/../routes/api.php');
        $this->loadViewsFrom(__DIR__. '/../resources/views', 'en');
    }

    public function register()
    {
    }
}
