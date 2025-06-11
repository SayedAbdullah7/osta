<?php

namespace App\Listeners;

use App\Events\ServiceCreated;
use App\Models\Provider;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyProvidersOfNewService
{
    protected NotificationService $notificationService;

    /**
     * Create the event listener.
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(ServiceCreated $event)
    {
        // Retrieve all providers.
        Provider::where('is_approved', true)->where('is_phone_verified', true)->chunk(100, function ($providers) use ($event) {
            foreach ($providers as $provider) {
                $this->notificationService->createNotification(
                    $provider,
                    'New Service Available',
                    "A new service (Name: {$event->service->name}) has been created. Check it out!",
                    'system',
                );
            }
        });
    }
}
