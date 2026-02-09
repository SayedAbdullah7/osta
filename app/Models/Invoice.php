<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $casts = [
        'details' => 'array',
    ];

    protected $with = ['purchases'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }


    /**
     * Define the relationship with the Payment model.
     */
    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payment::class);
    }
    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // Calculate payments and update status
    public function updatePaymentStatus(): void
    {
        $totalPaid = $this->payments()->sum('amount');

        if ($totalPaid < $this->total) {
            $this->payment_status = 'unpaid';
        } elseif ($totalPaid == $this->total) {
            $this->payment_status = 'paid';
        } elseif ($totalPaid > $this->total) {
            $this->payment_status = 'overpaid';
        }

        $this->paid = $totalPaid;
//        $this->save();
    }

    public function unpaidAmount(): float
    {
        return max(0, $this->total - $this->paid);
    }

    /**
     * Check if the invoice is fully paid.
     */
    public function isFullyPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Get the remaining balance for the invoice.
     */
    public function remainingBalance(): float
    {
        return $this->total - $this->payments()->sum('amount');
    }

    /**
     * Add a payment to the invoice.
     */
    public function addPayment(float $amount, string $paymentMethod = 'wallet', array $meta = [],$creatorId = null): Model
    {
        $payment = $this->payments()->create([
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'meta' => $meta,
            'creator_id' => $creatorId,
        ]);

        // Update payment status after adding payment
        $this->updatePaymentStatus();

        return $payment;
    }

    /**
     * Get the total amount of payments made to this invoice.
     */
    public function totalPaid(): float
    {
        return $this->payments()->sum('amount');
    }

    public function purchases(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(OrderDetail::class, 'order_id', 'order_id')->where('name', Message::PURCHASES);
    }
}
