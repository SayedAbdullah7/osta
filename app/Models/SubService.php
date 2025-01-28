<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
class SubService extends Model   implements HasMedia,TranslatableContract
{
    use HasFactory;
    use InteractsWithMedia;
    use Translatable;

    public array $translatedAttributes = ['name'];

    public function orders()
    {
        return $this->belongsToMany(Order::class)->withPivot('quantity');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    /**
     * The spaces that belong to the service.
     */
//    public function spaces(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
//    {
//        return $this->belongsToMany(Space::class)
//            ->withPivot('max_price'); // Include additional pivot columns
//    }

    public function spaces()
    {
        return $this->belongsToMany(Space::class, 'space_sub_service')
            ->withPivot('max_price');
    }
}
