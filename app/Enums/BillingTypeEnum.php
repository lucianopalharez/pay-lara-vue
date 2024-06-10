<?php

namespace App\Enums;

enum BillingTypeEnum: string
{
    case BOLETO = 'BOLETO';
    case CREDIT_CARD = 'CARTÃO DE CRÉDITO';
    case PIX = 'PIX';

    public static function values(): array
    {
        $cases = (array) self::cases();

        return array_map(function($item) {
            return [
                'name' => $item->name,
                'value' => $item->value
            ];
        }, $cases);
    }

};