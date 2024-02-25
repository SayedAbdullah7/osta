<?php

namespace App\Enums;

enum OrderStatusEnum
{

    public const PENDING = 'pending';
    public const ACCEPTED = 'accepted';
    public const COMING = 'coming';
    public const ALMOST_DONE = 'almost done';
    public const DONE = 'done';
    public const REJECTED = 'rejected';

}
