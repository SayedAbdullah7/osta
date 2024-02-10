<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;
use Laravel\Sanctum\HasApiTokens;

class Provider extends Authenticatable implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use HasApiTokens;


    public function services()
    {
        return $this->belongsToMany(Service::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function scopeVerified($query): void
    {
        $query->where('is_phone_verified', 1);
    }
    public function scopeNotVerified($query): void
    {
        $query->where('is_phone_verified', 0);
    }

    public function isVerified(): bool
    {
        return $this->is_phone_verified == 1;
    }
    public function changeToVerify(): void
    {
        $this->is_phone_verified = 1;
    }
}
