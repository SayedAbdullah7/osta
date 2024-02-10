<?php

namespace App\Observers;

use App\Models\Provider;
//use Illuminate\Support\Facades\Storage;

class ProviderObserver
{
    /**
     * Handle the Provider "created" event.
     */
    public function created(Provider $provider): void
    {
        //
    }

    /**
     * Handle the Provider "updated" event.
     */
    public function updated(Provider $provider): void
    {
        //
    }

    /**
     * Handle the Provider "deleted" event.
     */
    public function deleted(Provider $provider): void
    {
        \Illuminate\Support\Facades\Storage::put(time().'deleted.json', json_encode([$provider]));
        // Delete all media associated with the provider
        $provider->clearMediaCollection();
    }

    /**
     * Handle the Provider "deleting" event.
     *
     * @param  \App\Models\Provider  $provider
     * @return void
     */
    public function deleting(Provider $provider)
    {
        \Illuminate\Support\Facades\Storage::put(time().'deleting.json', json_encode([$provider]));
        // Delete all media associated with the provider
        $provider->clearMediaCollection();
    }
    /**
     * Handle the Provider "restored" event.
     */
    public function restored(Provider $provider): void
    {
        //
    }

    /**
     * Handle the Provider "force deleted" event.
     */
    public function forceDeleted(Provider $provider): void
    {
        //
    }
}
