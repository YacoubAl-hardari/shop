<?php

namespace App\Enums;

enum CustomerFinancialTransferStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'بانتظار التأكيد',
            self::APPROVED => 'تم الاستلام',
            self::REJECTED => 'مرفوض',
            self::CANCELLED => 'ملغي',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
            self::CANCELLED => 'gray',
        };
    }

    public static function toArray(): array
    {
        return [
            self::PENDING->value => self::PENDING->getLabel(),
            self::APPROVED->value => self::APPROVED->getLabel(),
            self::REJECTED->value => self::REJECTED->getLabel(),
            self::CANCELLED->value => self::CANCELLED->getLabel(),
        ];
    }
}
