<?php

namespace App\Enums;

enum PaymentTransactionStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'معلق',
            self::COMPLETED => 'مكتمل',
            self::FAILED => 'فاشل',
            self::CANCELLED => 'ملغي',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::COMPLETED => 'success',
            self::FAILED => 'danger',
            self::CANCELLED => 'gray',
        };
    }

    public static function toArray(): array
    {
        return [
            self::PENDING->value => self::PENDING->getLabel(),
            self::COMPLETED->value => self::COMPLETED->getLabel(),
            self::FAILED->value => self::FAILED->getLabel(),
            self::CANCELLED->value => self::CANCELLED->getLabel(),
        ];
    }
}

