<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id', 'month', 'orders_done_count', 'level', 'orders_remaining_for_next_level'
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
