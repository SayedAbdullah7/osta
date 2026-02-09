<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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

    protected $casts = [
        'month' => 'date',
        'completed_orders' => 'integer',
        'average_rating' => 'decimal:2',
        'repeat_customers' => 'integer',
        'completion_rate' => 'decimal:2',
        'cancellation_rate' => 'decimal:2',
        'response_time_avg' => 'decimal:2',
    ];

    protected $attributes = [
        'completed_orders' => 0,
        'average_rating' => 0,
        'repeat_customers' => 0,
        'completion_rate' => 0,
        'cancellation_rate' => 0,
        'response_time_avg' => 0,
    ];

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

    public function scopePreviousMonth($query, Carbon $date = null)
    {
        $date = $date ?? now();
        return $query->where('month', $date->copy()->subMonth()->startOfMonth());
    }
}
