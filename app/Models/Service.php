<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Service extends Model  implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    public function providers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Provider::class, 'provider_service');
    }

    public function subServices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SubService::class);
    }

    /**
     * The spaces that belong to the service.
     */
    public function spaces(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Space::class)
            ->withPivot('max_price'); // Include additional pivot columns
    }
}
