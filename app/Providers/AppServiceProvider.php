<?php

namespace App\Providers;

use App\Repositories\OrderRepositoryInterface;
use App\Repositories\OrderRepository;
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
        //
    }
}
