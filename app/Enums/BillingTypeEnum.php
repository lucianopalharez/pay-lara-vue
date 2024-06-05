<?php

namespace App\Enums;

enum BillingTypeEnum: string
{
    case BOLETO = 'BOLETO';
    case CREDIT_CARD = 'CREDIT_CARD';
    case PIX = 'PIX';

    public static function values(): Array
    {
        return array_column(self::cases(), 'value');
    }
};