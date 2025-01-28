<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class UserAction extends Model
{
    protected $fillable = ['action', 'model_id', 'value', 'user_id', 'provider_id'];

    // Constants for user types
    const USER_TYPE_USER = 'user';
    const USER_TYPE_PROVIDER = 'provider';

    // Constants for action names
    const ACTION_SHOW_RATE_LAST_ORDER = 'show_rate_last_order';

    // Generic method to update or create a user action
    public static function updateAction($userId, $action, $value, $modelId = null, $userType)
    {
        // Validate user type
        if (!in_array($userType, [self::USER_TYPE_USER, self::USER_TYPE_PROVIDER])) {
            throw new InvalidArgumentException('Invalid user type provided. Must be either "user" or "provider".');
        }

        // Check if an action already exists for this user and action type
        $query = self::where('action', $action);

        // Apply user-specific filter based on userType
        if ($userType === self::USER_TYPE_PROVIDER) {
            $query->where('provider_id', $userId);
        } elseif ($userType === self::USER_TYPE_USER) {
            $query->where('user_id', $userId);
        }

        // Only add the model_id condition if it's provided
//        if ($modelId !== null) {
//            $query->where('model_id', $modelId);
//        }

        $existingAction = $query->first();

        if (!$existingAction) {
            // If no action exists, create a new action record
            $existingAction = self::create([
                'action' => $action,       // Action type (e.g., 'rate_last_order', 'show_rate_last_order', etc.)
                'model_id' => $modelId,    // Model ID (e.g., order ID), can be null
                'value' => $value,         // Action value (e.g., true/false for rated/skipped)
                'user_id' => $userType === self::USER_TYPE_USER ? $userId : null, // Set user_id for user actions
                'provider_id' => $userType === self::USER_TYPE_PROVIDER ? $userId : null, // Set provider_id for provider actions
            ]);
        } else {
            // If the action exists, update it with the new data
            $existingAction->update([
                'value' => $value,         // Update value (true/false)
                'model_id' => $modelId,    // Optionally update model_id if provided
            ]);
        }

        if ($modelId != null){
            $cacheKey = self::generateCacheKey($userId, $action, $modelId, $userType);
            Cache::forget($cacheKey); // Remove the cached entry to ensure fresh data is fetched
        }
        // Clear the cache after updating the action
        $cacheKey = self::generateCacheKey($userId, $action, null, $userType);
        Cache::forget($cacheKey); // Remove the cached entry to ensure fresh data is fetched

        return $existingAction;
    }

    // Helper method to retrieve the action record (with caching)
    private static function getAction($userId, $action, $modelId = null, $userType)
    {
        // Generate a cache key based on the user, action, and model ID
        $cacheKey = self::generateCacheKey($userId, $action, $modelId, $userType);

        // Try to get the cached result first
        $cachedResult = Cache::get($cacheKey);

        if ($cachedResult !== null) {
            // If found in cache, return the cached value
            return $cachedResult;
        }

        // Build the query for the given userType and action
        $query = self::where('action', $action);

        // Filter by user type (provider or user)
        if ($userType === self::USER_TYPE_PROVIDER) {
            $query->where('provider_id', $userId);
        } elseif ($userType === self::USER_TYPE_USER) {
            $query->where('user_id', $userId);
        }

        // Apply model_id filter only if it's provided
        if ($modelId !== null) {
            $query->where('model_id', $modelId);
        }

        // Fetch the action record from the database
        $actionRecord = $query->latest()->first();

        // Cache the result for future use (set the cache expiration time as needed, e.g., 1 hour)
        Cache::put($cacheKey, $actionRecord, now()->addHours(1)); // Cache for 1 hour

        return $actionRecord;
    }


    // Specific method to show the rate action for a provider
    public static function showRateLastOrderForProvider($userId, $orderId)
    {
        return self::updateAction($userId, self::ACTION_SHOW_RATE_LAST_ORDER, true, $orderId, self::USER_TYPE_PROVIDER);
    }

    // Specific method to show the rate action for a user
    public static function showRateLastOrderForUser($userId, $orderId)
    {
        return self::updateAction($userId, self::ACTION_SHOW_RATE_LAST_ORDER, true, $orderId, self::USER_TYPE_USER);
    }

    // Specific method to hide the rate action for a provider
    public static function hideRateLastOrderForProvider($userId, $orderId)
    {
        return self::updateAction($userId, self::ACTION_SHOW_RATE_LAST_ORDER, false, null, self::USER_TYPE_PROVIDER);
    }

    // Specific method to hide the rate action for a user
    public static function hideRateLastOrderForUser($userId, $orderId)
    {
        return self::updateAction($userId, self::ACTION_SHOW_RATE_LAST_ORDER, false, null, self::USER_TYPE_USER);
    }




    // Get or create the show rate last order status for a provider
    public static function getShowRateLastOrderForProvider($userId)
    {
        return self::getAction($userId, self::ACTION_SHOW_RATE_LAST_ORDER, null, self::USER_TYPE_PROVIDER);
    }

    // Get or create the show rate last order status for a user
    public static function getShowRateLastOrderForUser($userId)
    {
        return self::getAction($userId, self::ACTION_SHOW_RATE_LAST_ORDER, null, self::USER_TYPE_USER);
    }


    // Helper method to generate a cache key based on the user, action, and model ID
    private static function generateCacheKey($userId, $action, $modelId, $userType)
    {
        // If model_id is null, we exclude it from the cache key
        if ($modelId !== null) {
            return "user_action_{$userType}_{$userId}_{$action}_{$modelId}";
        } else {
            return "user_action_{$userType}_{$userId}_{$action}";
        }
    }


    // Clear the action for a specific user and model (order)
    public static function clearAction($userId, $modelId, $action)
    {
        $actionRecord = self::where('action', $action)
            ->where('user_id', $userId)
            ->where('model_id', $modelId)
            ->first();

        if ($actionRecord) {
            $actionRecord->delete(); // Delete the action record if it exists
        }
    }


}
