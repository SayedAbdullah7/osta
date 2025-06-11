<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $guarded = [];

//    public function user()
//    {
//        return $this->belongsTo(User::class);
//    }

    /**
     * Get the owning notifiable model.
     */
    public function notifiable()
    {
        return $this->morphTo();
    }

    // Scope to filter unread notifications
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }


}
