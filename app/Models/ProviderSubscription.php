<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderSubscription extends Model
{
    protected $table = 'provider_subscription';
    protected $fillable = [
        'provider_id',
        'subscription_id',
        'start_date',
        'end_date',
    ];
    protected array $dates = [
        'start_date',
        'end_date',
    ];
    /**
     * Get the provider that owns the provider subscription.
     */
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * Get the subscription that is associated with the provider subscription.
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
