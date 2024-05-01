<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function members(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ConversationMember::class);
    }
}
