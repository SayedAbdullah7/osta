<?php

namespace App\Providers;

use App\Repositories\Interfaces\ReviewRepositoryInterface;
use App\Repositories\OfferRepository;
use App\Repositories\OrderRepositoryInterface;
use App\Repositories\OrderRepository;
use App\Repositories\ReviewRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
//        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(OfferRepository::class, function ($app) {
            return new OfferRepository();
        });

        $this->app->bind(OrderRepository::class, function ($app) {
            return new OrderRepository();
        });

        $this->app->bind(ReviewRepositoryInterface::class, ReviewRepository::class);


        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
        $this->app->singleton(\App\Services\FirebaseNotificationService::class, function ($app) {
            return new \App\Services\FirebaseNotificationService();
        });

        $this->app->singleton(\App\Services\SocketService::class, function ($app) {
            return new \App\Services\SocketService();
        });

        // Optionally, bind the NotificationManager itself if needed.
        $this->app->singleton(\App\Services\NotificationManager::class, function ($app) {
            $manager = new \App\Services\NotificationManager();
            // Automatically add default channels.
            $manager->addChannel(new \App\Notifications\Channels\DatabaseChannel($app->make(\App\Services\NotificationService::class)));
            $manager->addChannel(new \App\Notifications\Channels\FirebaseChannel($app->make(\App\Services\FirebaseNotificationService::class)));
            $manager->addChannel(new \App\Notifications\Channels\SocketChannel($app->make(\App\Services\SocketService::class)));
            return $manager;
        });



    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();
    }
}
