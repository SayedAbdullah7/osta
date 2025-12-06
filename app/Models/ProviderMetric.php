<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderMetric extends Model
{
    protected $fillable = [
        'provider_id',
        'month',
        'completed_orders',
        'average_rating',
        'repeat_customers',
        'completion_rate',
        'cancellation_rate',
        'response_time_avg'
    ];

    protected $dates = ['month'];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function scopeCurrentMonth($query)
    {
        return $query->where('month', now()->startOfMonth());
    }

    public function scopeForPeriod($query, $startDate, $endDate = null)
    {
        $endDate = $endDate ?? $startDate->copy()->endOfMonth();
        return $query->whereBetween('month', [$startDate, $endDate]);
    }
}
