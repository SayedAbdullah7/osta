<?php

namespace App\Enums;

use App\Enums\EnumToArray;

enum LocationNameEnum: string
{
    use EnumToArray;

    case Home = 'home';
    case Work = 'work';
    case Friend = 'friend';
    case Rest = 'rest';

}
