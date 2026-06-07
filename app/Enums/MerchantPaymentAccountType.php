<?php

namespace App\Enums;

enum MerchantPaymentAccountType: string
{
    case BANK = 'bank';
    case CARD = 'card';

    public function arabicLabel(): string
    {
        return match ($this) {
            self::BANK => 'بنك',
            self::CARD => 'بطاقة / محفظة',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type) => [$type->value => $type->arabicLabel()])
            ->all();
    }
}
