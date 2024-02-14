<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
//    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
//    {
//        return $this->hasMany(User::class);
//    }
    public function provider(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Provider::class);
    }
}
