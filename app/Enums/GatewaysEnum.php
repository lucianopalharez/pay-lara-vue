<?php

namespace App\Enums;

enum GatewaysEnum: string
{
    case ASASS = 'ASASS';

    public static function values(): Array
    {
        return array_column(self::cases(), 'value');
    }
};