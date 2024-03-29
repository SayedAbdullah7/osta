<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;
class Order extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    public function subServices()
    {
        return $this->belongsToMany(SubService::class)->withPivot('quantity');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function offers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Offer::class);
    }

    public function cancellationProviders(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Provider::class, 'order_cancellations');
    }

    public function scopePending($query): void
    {
        $query->where('status', OrderStatusEnum::PENDING);
    }
    public function scopeAvailableToAccept($query): void
    {
        $query->where('status', OrderStatusEnum::PENDING)->where('provider_id', null);
    }

    /**
     * Calculate the Haversine distance between two geographical coordinates.
     *
     * @param float $providerLatitude Latitude of the provider
     * @param float $providerLongitude Longitude of the provider
     * @return Expression
     */
    public static function calculateHaversineDistance(float $providerLatitude, float $providerLongitude): Expression
    {
        return DB::raw('ROUND((6371 * acos(cos(radians(' . $providerLatitude . ')) * cos(radians(locations.latitude)) * cos(radians(locations.longitude) - radians(' . $providerLongitude . ')) + sin(radians(' . $providerLatitude . ')) * sin(radians(locations.latitude)))), 2) AS distance');
    }

    public function isAvailableToAccept(): bool
    {
        return $this->status === OrderStatusEnum::PENDING;
    }
    public function isAvailableToCancel(): bool
    {
        return (!$this->provider_id && $this->status === OrderStatusEnum::PENDING);
    }

    public function isAvailableToSendOffer(): bool
    {
        return $this->status === OrderStatusEnum::PENDING;
    }
}
