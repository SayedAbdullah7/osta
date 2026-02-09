<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class Service extends Model  implements HasMedia ,TranslatableContract
//class Service extends Model  implements HasMedia
{
        use Translatable;
    use HasFactory;
    use InteractsWithMedia;

    // Define which attributes should be translatable
    public array $translatedAttributes = ['name'];

    // Optionally, you can specify your language fallback (for example English as default)
    public $locale = 'en';

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    public function providers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Provider::class, 'provider_service');
    }

    public function subServices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SubService::class);
    }

    /**
     * Get the sub service categories for the service.
     */
    public function subServiceCategories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SubServiceCategory::class);
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
