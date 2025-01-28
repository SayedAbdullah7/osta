<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;
//    protected $with = ['reviewable', 'reviewed'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

//    public function user()
//    {
//        return $this->belongsTo(User::class);
//    }
//
//    public function provider()
//    {
//        return $this->belongsTo(Provider::class);
//    }

    // The "reviewable" relationship (can be a user or provider)
    public function reviewable()
    {
        return $this->morphTo();
    }

    public function reviewer()
    {
        return $this->morphTo();
    }

    // The "reviewed" relationship (can be a user or provider)
    public function reviewed()
    {
        return $this->morphTo();
    }

    public function scopeApproved( $query)
    {
        return $query->where('is_approved', true);
    }

    // Define a scope for unapproved records
    public function scopeUnapproved( $query)
    {
        return $query->where('is_approved', false);
    }

}
