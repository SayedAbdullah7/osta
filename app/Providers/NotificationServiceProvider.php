<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\FirebaseNotificationService;
use App\Services\SocketService;
use App\Services\NotificationService;
use App\Services\NotificationManager;
use App\Notifications\Channels\DatabaseChannel;
use App\Notifications\Channels\FirebaseChannel;
use App\Notifications\Channels\SocketChannel;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        // Bind FirebaseNotificationService as a singleton.
        $this->app->singleton(FirebaseNotificationService::class, function ($app) {
            return new FirebaseNotificationService();
        });

        // Bind SocketService as a singleton.
        $this->app->singleton(SocketService::class, function ($app) {
            return new SocketService();
        });

        // Bind NotificationService as a singleton.
        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService();
        });

        // Bind NotificationManager as a singleton and add default channels.
        $this->app->singleton(NotificationManager::class, function ($app) {
            $manager = new NotificationManager();

            // Get the list of default channels from the configuration.
            $defaultChannels = config('notifications.default_channels', []);

            // Add DatabaseChannel if enabled.
            if (in_array('database', $defaultChannels)) {
                $manager->addChannel(new DatabaseChannel(
                    $app->make(NotificationService::class)
                ));
            }

            // Add FirebaseChannel if enabled.
            if (in_array('firebase', $defaultChannels)) {
                $manager->addChannel(new FirebaseChannel(
                    $app->make(FirebaseNotificationService::class)
                ));
            }

            // Add SocketChannel if enabled.
            if (in_array('socket', $defaultChannels)) {
                $manager->addChannel(new SocketChannel(
                    $app->make(SocketService::class)
                ));
            }

            return $manager;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        //
    }
}
