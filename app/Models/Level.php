<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory;

//    protected $fillable = ['name','level', 'orders_required', 'next_level_id'];
//
//    /**
//     * Set the next level and level number automatically when a new level is created or updated.
//     */
//    public static function booted()
//    {
//        parent::boot();
//
//        static::created(function ($level) {
//            // Recalculate the level and next_level_id after a new level is created
//            self::calculateLevels();
//        });
//
//        static::updated(function ($level) {
//            // Recalculate the level and next_level_id when the level is updated
//            self::calculateLevels();
//        });
//    }
//    /**
//     * Automatically calculate and set the next level id and the level number.
//     */
//    public static function calculateLevels()
//    {
//        // Get all levels ordered by 'orders_required' to determine the correct level sequence
//        $levels = self::orderBy('orders_required')->get();
//
//        // Loop through each level to set its 'level' and 'next_level_id'
//        foreach ($levels as $index => $level) {
//            // Set the 'level' number based on the position in the ordered list
//            $level->level = $index + 1;
//            // Set the 'next_level_id' to the next level in the sequence or null for the last level
//            $level->next_level_id = isset($levels[$index + 1]) ? $levels[$index + 1]->id : null;
//            $level->save();  // Save the updated level
//        }
//    }
//
//    public function nextLevel(): \Illuminate\Database\Eloquent\Relations\BelongsTo
//    {
//        return $this->belongsTo(__CLASS__, 'next_level_id');
//    }
//
//    public function providerStatistics()
//    {
//        return $this->hasMany(ProviderStatistic::class);
//    }



    protected $fillable = [
        'name',
        'slug',
        'level',
        'badge_image',
        'requirements',
        'benefits',
        'is_active',
// for grace period
        'grace_period_months',
        'grace_period_applies_to_orders_only'
    ];

    protected $casts = [
        'requirements' => 'array',
        'benefits' => 'array',
        'is_active' => 'boolean'
    ];

    // for new logic grace period
    public function getGracePeriodEndDate(Carbon|string $achievedAt): Carbon
    {
        if (is_string($achievedAt)) {
            $achievedAt = Carbon::parse($achievedAt);
        }
        return $achievedAt->addMonths($this->grace_period_months);
    }

    public function hasGracePeriod(): bool
    {
        return $this->grace_period_months > 0;
    }
    // end of new logic grace period

    public function providers()
    {
        return $this->belongsToMany(Provider::class, 'provider_levels')
            ->withPivot('achieved_at', 'valid_until', 'is_current')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function nextLevel()
    {
        return self::where('level', '>', $this->level)
            ->active()
            ->orderBy('level')
            ->first();
    }

    public function previousLevel()
    {
        return self::where('level', '<', $this->level)
            ->active()
            ->orderByDesc('level')
            ->first();
    }

    public function getRequirementsAttribute($value)
    {
        return json_decode($value, true) ?? [
            'metrics' => [],
            'duration' => null
        ];
    }

    public function getBenefitsAttribute($value)
    {
        return json_decode($value, true) ?? [
            'commission_rate' => 1.0,
            'features' => []
        ];
    }
}
