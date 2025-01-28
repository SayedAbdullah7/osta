<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Spatie\MediaLibrary\InteractsWithMedia;

class Warranty extends Model implements TranslatableContract
{
    use HasFactory;
    use Translatable;
    use InteractsWithMedia;
    // Define fillable attributes
    protected $fillable = [
        'name',
        'description',
        'duration_months',
        'percentage_cost',
    ];
    public array $translatedAttributes = ['name', 'description'];

    /**
     * Calculate the cost of the warranty based on the product price.
     *
     * @param float $orderPrice
     * @return float
     */
    public function calculateCost($orderPrice): float
    {
        return ($this->percentage_cost / 100) * $orderPrice;
    }


}
