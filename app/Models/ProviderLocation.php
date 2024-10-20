<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderLocation extends Model
{
    use HasFactory;

    protected $fillable = ['provider_id', 'latitude', 'longitude', 'tracked_at'];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
