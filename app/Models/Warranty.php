<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warranty extends Model
{
    use HasFactory;

    // Define fillable attributes
    protected $fillable = [
        'name',
        'description',
        'duration_months',
        'percentage_cost',
    ];

    /**
     * Calculate the cost of the warranty based on the product price.
     *
     * @param float $orderPrice
     * @return float
     */
    public function calculateCost($orderPrice): float
    {
        return ($this->percentage_cost / 100) * $orderPrice;
    }


}
