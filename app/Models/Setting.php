<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value'];

    // Cache TTL (time to live) in seconds, making it configurable
    public const CACHE_TTL = 60 * 60 * 24; // 1 day


    /**
     * Clear the cache after creating a setting.
     *
     * @return void
     */
    protected static function booted()
    {
        parent::boot();

        // Clear cache when a setting is created
        static::created(function ($setting) {
            Cache::forget(self::getCacheKey());  // Clear the entire settings cache
//            Cache::forget("setting_{$setting->key}");  // Clear the specific setting cache
        });

        // Clear cache when a setting is updated
        static::updated(function ($setting) {
            Cache::forget(self::getCacheKey());  // Clear the entire settings cache
            Cache::forget("setting_{$setting->key}");  // Clear the specific setting cache
        });

        // Clear cache when a setting is deleted
        static::deleted(function ($setting) {
            Cache::forget(self::getCacheKey());  // Clear the entire settings cache
            Cache::forget("setting_{$setting->key}");  // Clear the specific setting cache
        });
    }


    /**
     * Get a setting by its key.
     * Returns a default value if the key is not found.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getSetting($key, $default = null)
    {
        // Cache the setting for faster access
        return Cache::rememberForever("setting_{$key}", function () use ($key) {
            return self::where('key', $key)->first();
        })?->value ?? $default;
    }

    /**
     * Get all settings from cache.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getAllSettings()
    {
        // Cache all settings for 24 hours, this can be adjusted.
        return Cache::remember(self::getCacheKey(), self::CACHE_TTL, function () {
            return Setting::all()->pluck('value', 'key');
        });
    }

    /**
     * Update a setting value and reset the cache for that setting.
     *
     * @param string $key
     * @param mixed $value
     * @return Setting
     */
    public static function updateSetting($key, $value): Setting
    {
        $setting = self::updateOrCreate(['key' => $key], ['value' => $value]);

        // Reset individual cache for the setting after updating
        Cache::forget("setting_{$key}");

        return $setting;
    }

    /**
     * Get the cache key for all settings.
     *
     * @return string
     */
    private static function getCacheKey()
    {
        return 'settings';
    }
//
//    /**
//     * Accessor for value attribute: Automatically decode JSON values if needed.
//     *
//     * @param string $value
//     * @return mixed
//     */
//    public function getValueAttribute($value)
//    {
//        // Automatically decode JSON if the setting is of type 'json'
//        return json_decode($value, true) ?: $value;
//    }
//
//    /**
//     * Mutator for value attribute: Automatically encode JSON values when setting.
//     *
//     * @param mixed $value
//     * @return void
//     */
//    public function setValueAttribute($value)
//    {
//        // Automatically encode arrays to JSON if needed
//        $this->attributes['value'] = is_array($value) ? json_encode($value) : $value;
//    }
}
