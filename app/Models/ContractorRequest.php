<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractorRequest extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'phone',
        'description',
        'space',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
