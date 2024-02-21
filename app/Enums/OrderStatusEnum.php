<?php

namespace App\Enums;

enum OrderStatusEnum
{
    public const PENDING = 'pending';
    public const ACCEPTED = 'accepted';
    public const DONE = 'done';

    public const REJECTED = 'rejected';
    public const COMING = 'coming';
}
