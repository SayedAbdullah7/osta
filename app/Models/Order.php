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

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status' => OrderStatusEnum::class,
    ];

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

    public function provider()
    {
        return $this->belongsTo(Provider::class);
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
        return DB::raw('ROUND((6371 * acos(cos(radians(' . $providerLatitude . ')) * cos(radians(location_latitude)) * cos(radians(location_longitude) - radians(' . $providerLongitude . ')) + sin(radians(' . $providerLatitude . ')) * sin(radians(location_latitude)))), 2) AS distance');
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

    public function maxAllowedOfferPrice(): float|int
    {
        if ($this->subServices->count() > 0) {
            $max = 0;
            foreach ($this->subServices as $subService) {
                $max += ($subService->pivot->quantity * $subService->max_price);
            }
            return $max;
        }
        return INF;
    }

    public function conversation(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(Conversation::class, 'model');
    }

    public function isPreview(): bool
    {
        return $this->price == 0 || $this->price == null;
    }

}
