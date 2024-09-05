<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order;

class Conversation extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function model()
    {
        return $this->morphTo();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function lastMessage(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Message::class)->latest('id');
    }

    public function members(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ConversationMember::class);
    }

    public function order()
    {
        return $this->morphTo()->where('model_type', Order::class);
    }
//    public function users()
//    {
//        return $this->morphedByMany(User::class, 'userable', 'conversation_members')->first();
//    }

    public function users(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphedByMany(User::class, 'user', 'conversation_members');
    }
//    public function user()
//    {
//        return $this->morphOne(User::class, 'user', 'conversation_members');
//        }


    public function providers(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphedByMany(Provider::class, 'user', 'conversation_members');
    }
}
