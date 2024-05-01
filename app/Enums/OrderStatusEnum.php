<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    use EnumToArray;

    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case COMING = 'coming';
    case ALMOST_DONE = 'almost done';
    case DONE = 'done';
    case REJECTED = 'rejected';


}
