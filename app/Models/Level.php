<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory;

    protected $fillable = ['name','level', 'orders_required', 'next_level_id'];

    /**
     * Set the next level and level number automatically when a new level is created or updated.
     */
    public static function booted()
    {
        parent::boot();

        static::created(function ($level) {
            // Recalculate the level and next_level_id after a new level is created
            self::calculateLevels();
        });

        static::updated(function ($level) {
            // Recalculate the level and next_level_id when the level is updated
            self::calculateLevels();
        });
    }
    /**
     * Automatically calculate and set the next level id and the level number.
     */
    public static function calculateLevels()
    {
        // Get all levels ordered by 'orders_required' to determine the correct level sequence
        $levels = self::orderBy('orders_required')->get();

        // Loop through each level to set its 'level' and 'next_level_id'
        foreach ($levels as $index => $level) {
            // Set the 'level' number based on the position in the ordered list
            $level->level = $index + 1;
            // Set the 'next_level_id' to the next level in the sequence or null for the last level
            $level->next_level_id = isset($levels[$index + 1]) ? $levels[$index + 1]->id : null;
            $level->save();  // Save the updated level
        }
    }

    public function nextLevel(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(__CLASS__, 'next_level_id');
    }

    public function providerStatistics()
    {
        return $this->hasMany(ProviderStatistic::class);
    }
}
