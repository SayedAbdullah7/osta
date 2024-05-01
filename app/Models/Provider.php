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
class Provider extends Authenticatable implements HasMedia,Wallet
{
    use HasFactory;
    use InteractsWithMedia;
    use HasApiTokens;
    use HasWallet;

    protected $guarded = [];


    public function services(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Service::class,'provider_service');
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
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->first_name,
        );
    }
}
