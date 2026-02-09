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
    use InteractsWithMedia;

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

    public function scopePreviewCost($query)
    {
        return $query->where('name', Message::ACTION_CONVERT_TO_PREVIEW);
    }
}
