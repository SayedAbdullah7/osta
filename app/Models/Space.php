<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Space extends Model
{
    use HasFactory;


    /**
     * The services that belong to the space.
     */
    public function services(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Service::class)
            ->withPivot('max_price'); // Include additional pivot columns
    }

    public function subServices(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(SubService::class)
            ->withPivot('max_price'); // Include additional pivot columns
    }
}
