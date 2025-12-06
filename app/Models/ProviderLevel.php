<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderLevel extends Model
{
    protected $table = 'provider_levels';

    protected $fillable = [
        'provider_id',
        'level_id',
        'achieved_at',
        'valid_until',
        'is_current'
    ];

//    protected $dates = [
//        'achieved_at',
//        'valid_until'
//    ];

    protected $casts = [
        'achieved_at' => 'datetime',
        'valid_until' => 'datetime',
        'is_current' => 'boolean',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }
}
