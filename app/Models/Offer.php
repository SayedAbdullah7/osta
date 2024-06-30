<?php

namespace App\Models;

use App\Enums\OfferStatusEnum;
use App\Enums\OrderCategoryEnum;
use App\Scopes\CustomSoftDeletingScope;
use App\Traits\NewSoftDeletes;
use App\Traits\SoftDeletesWithFutureDeletion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Offer extends Model
{
    use HasFactory;
//    use SoftDeletes;
//    use SoftDeletesWithFutureDeletion;
//    use SoftDeletes;

//    use NewSoftDeletes;

//    protected static function boot()
//    {
//        parent::boot();
//
//        static::addGlobalScope(new CustomSoftDeletingScope);
//    }
    protected $guarded = [];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function scopePending($query): void
    {
        $query->where('status', OfferStatusEnum::PENDING);
    }
}
