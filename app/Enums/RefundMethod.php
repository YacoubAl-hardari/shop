<?php

namespace App\Enums;

enum RefundMethod: string
{
    case CASH              = 'cash';               // نقد للعميل
    case CREDIT_NOTE       = 'credit_note';        // رصيد دائن في حساب العميل
    case REDUCE_RECEIVABLE = 'reduce_receivable';  // خصم من ذمة العميل / كشف الحساب
    case SPLIT_SETTLEMENT  = 'split_settlement';  // تسوية جزئية: ذمة + نقد
    case NONE              = 'none';               // بدون استرداد (للاستبدال بنفس القيمة)

    public function label(): string
    {
        return match ($this) {
            self::CASH              => 'نقد',
            self::CREDIT_NOTE       => 'رصيد دائن للعميل',
            self::REDUCE_RECEIVABLE => 'خصم من كشف الحساب',
            self::SPLIT_SETTLEMENT  => 'تسوية تلقائية',
            self::NONE              => 'بدون استرداد',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $t) => [$t->value => $t->label()])
            ->all();
    }
}
