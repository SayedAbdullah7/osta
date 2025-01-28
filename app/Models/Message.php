<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Message extends Model implements HasMedia
{
    use InteractsWithMedia;
    public const ACTION_CONFIRM_ORDER = 'confirm_order';
    public const ACTION_ADDITIONAL_COST = 'additional_cost';
    public const PURCHASES = 'purchases';
    public const ACTION_PAY = 'pay';
    public const ACTION_CONVERT_TO_OFFER = 'convert_to_offer';
    public const ACTION_CONVERT_TO_PREVIEW = 'convert_to_preview';

//    protected $fillable = ['content', 'conversation_id', 'sender_id', 'sender_type'];
    protected $guarded = [];
    protected $casts = [
        'options' => 'array'
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->morphTo();
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(184)
            ->height(116);
    }
}
