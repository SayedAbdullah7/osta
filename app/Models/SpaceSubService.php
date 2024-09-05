<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpaceSubService extends Model
{
    use HasFactory;

    // Specify the table name if it's different from the plural form of the model name
    protected $table = 'space_sub_service';

    // Since this table doesn't have an auto-incrementing ID, disable the incrementing property
    public $incrementing = false;

    // Define the primary key(s)
    protected $primaryKey = ['space_id', 'sub_service_id'];

    // If using a composite primary key, this is necessary
    protected $keyType = 'array';

    // Define the fillable fields
    protected $fillable = ['space_id', 'sub_service_id', 'max_price'];

    // Disable timestamps if not used
    public $timestamps = false;

    // Set up relationships, e.g., with Space and SubService models
    public function space()
    {
        return $this->belongsTo(Space::class);
    }

    public function subService()
    {
        return $this->belongsTo(SubService::class);
    }
}
