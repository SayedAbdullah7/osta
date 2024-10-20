<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderSubService extends Model
{
    use HasFactory;

    protected $table = 'order_sub_service';

    protected $with = ['space','subService'];

    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function subService(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SubService::class);
    }

    public function space(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

}
