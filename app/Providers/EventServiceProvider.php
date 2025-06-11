<?php

namespace App\Providers;

use App\Events\OrderPaidByUserEvent;
use App\Listeners\OrderPaidByUserListener;
use App\Models\Provider;
use App\Observers\ProviderObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        \App\Events\ServiceCreated::class => [
//            \App\Listeners\NotifyProvidersOfNewService::class,
            \App\Listeners\NotifyUsersOfNewService::class,
        ],
        OrderPaidByUserEvent::class => [
            OrderPaidByUserListener::class,
        ],
        \App\Events\ProviderLevelUp::class => [
            \App\Listeners\SendLevelUpNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        Provider::observe(ProviderObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
