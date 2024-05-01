<?php

namespace App\Enums;


enum OrderCategoryEnum: string
{
    use EnumToArray;
    case Basic = 'basic';
    case SpaceBased = 'space_based';
    case Technical = 'technical';
    case Other = 'other';


}
