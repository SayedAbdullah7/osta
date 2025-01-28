<?php

namespace App\Models;

use App\Enums\OfferStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class OrderDetail extends Model  implements HasMedia
{
    use HasFactory;
    use HasFactory;
    use InteractsWithMedia;

//    const NAME_ADDITIONAL_COST = 'additional_cost';

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeAdditionalCost($query)
    {
        return $query->where('name', Message::ACTION_ADDITIONAL_COST);
    }

    public function scopePurchases($query)
    {
        return $query->where('name', Message::PURCHASES);
    }
}
