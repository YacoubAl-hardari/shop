<?php

namespace App\Enums;

enum SalePaymentType: string
{
    case CASH = 'cash';
    case CREDIT = 'credit';
    case PARTIAL = 'partial';

    public function arabicLabel(): string
    {
        return match ($this) {
            self::CASH => 'نقد',
            self::CREDIT => 'آجل',
            self::PARTIAL => 'جزئي',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type) => [$type->value => $type->arabicLabel()])
            ->all();
    }
}
