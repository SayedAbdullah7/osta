<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubService extends Model
{
    use HasFactory;

    public function orders()
    {
        return $this->belongsToMany(Order::class)->withPivot('quantity');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    /**
     * The spaces that belong to the service.
     */
//    public function spaces(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
//    {
//        return $this->belongsToMany(Space::class)
//            ->withPivot('max_price'); // Include additional pivot columns
//    }

    public function spaces()
    {
        return $this->belongsToMany(Space::class, 'space_sub_service')
            ->withPivot('max_price');
    }
}
