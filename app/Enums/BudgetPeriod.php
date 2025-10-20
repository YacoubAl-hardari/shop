<?php

namespace App\Enums;

enum BudgetPeriod: string
{
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';
    case CUSTOM = 'custom';

    public function getLabel(): string
    {
        return match ($this) {
            self::DAILY => 'يومي',
            self::WEEKLY => 'أسبوعي',
            self::MONTHLY => 'شهري',
            self::YEARLY => 'سنوي',
            self::CUSTOM => 'مخصص',
        };
    }

    public function getDays(): int
    {
        return match ($this) {
            self::DAILY => 1,
            self::WEEKLY => 7,
            self::MONTHLY => 30,
            self::YEARLY => 365,
            self::CUSTOM => 0, // يتم تحديده يدوياً
        };
    }

    public static function toArray(): array
    {
        return [
            self::DAILY->value => self::DAILY->getLabel(),
            self::WEEKLY->value => self::WEEKLY->getLabel(),
            self::MONTHLY->value => self::MONTHLY->getLabel(),
            self::YEARLY->value => self::YEARLY->getLabel(),
            self::CUSTOM->value => self::CUSTOM->getLabel(),
        ];
    }
}
