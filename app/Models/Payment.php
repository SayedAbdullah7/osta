<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'invoice_id',       // The ID of the related invoice
        'amount',           // The payment amount
        'payment_method',   // The payment method (e.g., 'wallet', 'credit card', etc.)
        'meta',             // Additional data as JSON (e.g., 'description' or other metadata)
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'meta' => 'array',  // Automatically cast the meta field as an array
    ];

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'creator_id');
    }

    // Payment belongs to a reviewer (Admin)
    public function reviewer()
    {
        return $this->belongsTo(Admin::class, 'reviewer_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
