<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    use HasFactory;

    // Fillable fields for mass assignment
    protected $fillable = [
        'is_set_notification',
        'token',
        'is_ios',
        'user_id',
        'provider_id',
    ];

    // Cast attributes to specific types
    protected $casts = [
        'is_set_notification' => 'boolean',
        'is_ios' => 'boolean',
//        'created_at' => 'datetime',
//        'updated_at' => 'datetime',
    ];

    // Define relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    // Scopes to filter by notification status
    public function scopeSetNotification($query)
    {
        return $query->where('is_set_notification', true);
    }

    public function scopeUnsetNotification($query)
    {
        return $query->where('is_set_notification', false);
    }

    // Scopes for iOS and Android
    public function scopeIos($query)
    {
        return $query->where('is_ios', 1);
    }

    public function scopeAndroid($query)
    {
        return $query->where('is_ios', 0);
    }




//    public function userable(): \Illuminate\Database\Eloquent\Relations\MorphTo
//    {
//        return $this->morphTo();
//    }
}
