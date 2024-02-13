<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;
    protected $guarded = [];


    public function provider(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

}
