<?php

namespace App\Enums;

enum CustomerFinancialTransferPurpose: string
{
    case SETTLEMENT = 'settlement';
    case PREPAID = 'prepaid';

    public function getLabel(): string
    {
        return match ($this) {
            self::SETTLEMENT => 'سداد',
            self::PREPAID => 'إضافة رصيد فائض',
        };
    }

    public static function toArray(): array
    {
        return [
            self::SETTLEMENT->value => self::SETTLEMENT->getLabel(),
            self::PREPAID->value => self::PREPAID->getLabel(),
        ];
    }
}
