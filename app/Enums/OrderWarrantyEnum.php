<?php

namespace App\Enums;

use App\Enums\EnumToArray;

enum OrderWarrantyEnum: string
{
    use EnumToArray;

    case ONE = '1';
    case TWO = '2';
    case THREE = '3';

}
