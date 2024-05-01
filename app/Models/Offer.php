<?php

namespace App\Models;

use App\Enums\OrderCategoryEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;
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
        $query->where('status', OrderCategoryEnum::PENDING);
    }
}
