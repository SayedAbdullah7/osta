<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Wallet;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements Wallet,HasMedia
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasWallet;
    use InteractsWithMedia;



    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

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
    public function city()
    {
        return $this->belongsTo(City::class);
    }
    public function country(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
    public function locations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Location::class);
    }
    public function default_location(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Location::class)->where('is_default',true);
    }

    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class);
    }

//    public function device_tokens(): \Illuminate\Database\Eloquent\Relations\MorphMany
//    {
//        return $this->morphMany(DeviceToken::class,'userable');
//    }
//
//    public  function messengerColor(): Attribute
//    {
//        return Attribute::make(
//            get: fn (string $value) => 'a'),
//        );
//    }
    public function conversations()
    {
        return $this->morphToMany(Conversation::class, 'user', 'conversation_members');
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
                return [
                    'total_reviews' => 0,
                    'average_rating' => 0.00,
                    'completed_orders' => 0
                ];
            });
    }

    public function hasActiveOrders(): bool
    {
        return $this->orders()->where('status', OrderStatusEnum::ACCEPTED)->exists();
    }

    public function definition()
    {
        return $this->name . ' ( user )';
    }

    public function getFullNameAttribute(): string
    {
        return $this->name;
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
