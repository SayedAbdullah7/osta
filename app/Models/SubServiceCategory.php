<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubServiceCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'order',
        'service_id',
    ];

    /**
     * Get the service that owns the category.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the sub services for the category.
     */
    public function subServices()
    {
        return $this->hasMany(SubService::class);
    }
}
