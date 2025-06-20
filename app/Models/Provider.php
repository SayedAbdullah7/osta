<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;
use Laravel\Sanctum\HasApiTokens;
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Wallet;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Provider extends Authenticatable implements HasMedia, Wallet
{
    use HasFactory;
    use InteractsWithMedia;
    use HasApiTokens;
    use HasWallet;

    protected $guarded = [];
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
//    protected $casts = [
//        'is_new' => 'boolean',
//    ];

    public function getGenderLabelAttribute(): string
    {
        return $this->gender ? 'Male' : 'Female';
    }

    public function services(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'provider_service');
    }

    public function city(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function country(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function bank_account(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(BankAccount::class);
    }

    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function offers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Offer::class);
    }

//    public function orderCancellations()
//    {
//        return $this->hasMany(OrderCancellation::class);
//    }


    public function scopeVerified($query): void
    {
        $query->where('is_phone_verified', 1);
    }

    public function scopeNotVerified($query): void
    {
        $query->where('is_phone_verified', 0);
    }

    public function isVerified(): bool
    {
        return $this->is_phone_verified == 1;
    }

    public function changeToVerify(): void
    {
        $this->is_phone_verified = 1;
    }

    /**
     * Get the user's first name.
     */
//    protected function name(): Attribute
//    {
//        return Attribute::make(
//            get: fn() => $this->first_name,
//        );
//    }

//    public function reviewStatistics()
//    {
//        return $this->hasOne(ProviderReviewStatistics::class)->withDefault([
//            'total_reviews' => 0,
//            'average_rating' => 0.00,
//            'completed_orders'=>0
//        ]);
//    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function statistics(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProviderStatistic::class);
    }

    public function currentMonthStatistic(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProviderStatistic::class)
            ->where('month', now()->startOfMonth());
    }

    public function subscriptions()
    {
        return $this->belongsToMany(Subscription::class, 'provider_subscription')
            ->withPivot('start_date', 'end_date')
            ->withTimestamps();
    }

    public function providerSubscriptions()
    {
        return $this->hasMany(ProviderSubscription::class);
    }

    public function location()
    {
        return $this->hasOne(ProviderLocation::class);
    }

    public function reviewsWritten(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function reviewsReceived(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Review::class, 'reviewed');
    }

    public function reviewStatistics()
    {
        return $this->morphOne(ReviewStatistic::class, 'reviewable')->withDefault(static function () {
            return (object)[
                'total_reviews' => 0,
                'average_rating' => '0',
                'completed_orders' => 0
            ];
        });
    }

    public function definition()
    {
        return $this->name . ' ( provider )';
//        return $this->first_name . ' ' . $this->last_name . ' ( provider )';
    }

    public function getFullNameAttribute(): string
    {
        return $this->name;
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getShortNameAttribute(): string
    {
        return $this->name;
    }

    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function deletionRequests()
    {
        return $this->morphMany(AccountDeletionRequest::class, 'deletable');
    }
}
