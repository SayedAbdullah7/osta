<?php

namespace App\Events;

use App\Models\Provider;
use App\Models\Level;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProviderLevelDemoted
{
    use Dispatchable, SerializesModels;

    public Provider $provider;
    public Level $oldLevel;
    public ?Level $newLevel;

    /**
     * Create a new event instance.
     */
    public function __construct(Provider $provider, Level $oldLevel, ?Level $newLevel = null)
    {
        $this->provider = $provider;
        $this->oldLevel = $oldLevel;
        $this->newLevel = $newLevel;
    }
}
