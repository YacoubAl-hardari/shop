<?php

namespace App\Enums;

enum AccountType: string
{
    case ASSET = 'asset';
    case LIABILITY = 'liability';
    case EQUITY = 'equity';
    case REVENUE = 'revenue';
    case EXPENSE = 'expense';

    public function arabicLabel(): string
    {
        return match ($this) {
            self::ASSET => 'أصول',
            self::LIABILITY => 'خصوم',
            self::EQUITY => 'حقوق ملكية',
            self::REVENUE => 'إيرادات',
            self::EXPENSE => 'مصروفات',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type) => [$type->value => $type->arabicLabel()])
            ->all();
    }
}
