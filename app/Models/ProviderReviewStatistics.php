<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderReviewStatistics extends Model
{
//    use HasFactory;

    protected $fillable = [
        'provider_id',
        'total_reviews',
        'average_rating',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
