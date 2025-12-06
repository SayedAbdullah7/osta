<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order;

class Conversation extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Conversation Model
    public function unreadMessagesCountForUser($user)
    {
        return $this->messages()
            ->where(function ($query) use ($user) {
                $query->where('sender_id', '!=', $user->id) // Exclude messages sent by the user
                ->orWhere('sender_type', '!=', get_class($user)); // Exclude messages sent by the user class type
            })
            ->where('is_read', false)->count(); // Only unread messages
    }


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
