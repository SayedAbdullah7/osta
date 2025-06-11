<?php

namespace App\Listeners;

use App\Events\ProviderLevelUp;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendLevelUpNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ProviderLevelUp $event): void
    {
        $provider = $event->providerStatistics->provider;
//        $provider->notify(new LevelUpNotification($event->oldLevel, $event->newLevel));
    }
}
