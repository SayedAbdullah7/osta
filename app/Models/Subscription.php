<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Spatie\MediaLibrary\HasMedia;

class Subscription extends Model
{
    use HasFactory;
//    use Translatable;

    protected $fillable = [
        'name', 'description', 'price', 'price_before_discount',
        'discount_expiration_date', 'level_id', 'fee_percentage',
        'number_of_days', 'is_available',
    ];

    public array $translatedAttributes = ['name','description'];

    // Subscription belongs to a level
    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    // Subscription has many providers
    public function providers()
    {
        return $this->belongsToMany(Provider::class, 'provider_subscription')
            ->withPivot('start_date', 'end_date')
            ->withTimestamps();
    }
}

