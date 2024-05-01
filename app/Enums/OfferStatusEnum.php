<?php

namespace App\Enums;

enum OfferStatusEnum: string
{
    use EnumToArray;

    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';

    public function default(): OfferStatusEnum
    {
        return self::PENDING;
    }
    public static function defaultValue(): string
    {
        return self::PENDING->value;
    }
}
