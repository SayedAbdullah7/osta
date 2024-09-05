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




    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();
    }
}
