<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;


    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function city()
    {
        return $this->belongsTo(City::class);
    }
    public function isDefault(): bool
    {
        return $this->is_default == 1;
    }
    public function changeToDefault(): void
    {
        $this->is_default = 1;
    }

    public function scopeDefault($query): void
    {
        $query->where('is_default', 1);
    }


}
