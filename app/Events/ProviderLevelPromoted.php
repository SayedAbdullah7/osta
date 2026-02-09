<?php

namespace App\Events;

use App\Models\Provider;
use App\Models\Level;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProviderLevelPromoted
{
    use Dispatchable, SerializesModels;

    public Provider $provider;
    public Level $newLevel;
    public ?Level $oldLevel;

    /**
     * Create a new event instance.
     */
    public function __construct(Provider $provider, Level $newLevel, ?Level $oldLevel = null)
    {
        $this->provider = $provider;
        $this->newLevel = $newLevel;
        $this->oldLevel = $oldLevel;
    }
}
