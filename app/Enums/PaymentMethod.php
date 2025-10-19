<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case BANK_TRANSFER = 'bank_transfer';
    case CASH = 'cash';
    case CHECK = 'check';
    case CARD = 'card';
    case WALLET = 'wallet';

    public function getLabel(): string
    {
        return match ($this) {
            self::BANK_TRANSFER => 'تحويل بنكي',
            self::CASH => 'نقدي',
            self::CHECK => 'شيك',
            self::CARD => 'بطاقة ائتمان',
            self::WALLET => 'محفظة رقمية',
        };
    }

    public static function toArray(): array
    {
        return [
            self::BANK_TRANSFER->value => self::BANK_TRANSFER->getLabel(),
            self::CASH->value => self::CASH->getLabel(),
            self::CHECK->value => self::CHECK->getLabel(),
            self::CARD->value => self::CARD->getLabel(),
            self::WALLET->value => self::WALLET->getLabel(),
        ];
    }
}

