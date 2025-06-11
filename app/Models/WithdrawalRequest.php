<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    use HasFactory;

    protected $fillable = ['provider_id', 'amount', 'status', 'payment_method', 'payment_details'];

    protected $casts = [
        'payment_details' => 'array',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
