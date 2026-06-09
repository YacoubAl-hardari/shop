<?php

namespace App\Enums;

enum ReturnType: string
{
    case RETURN   = 'return';   // إرجاع مع استرداد مبلغ أو رصيد
    case EXCHANGE = 'exchange'; // استبدال ببضاعة بديلة

    public function label(): string
    {
        return match ($this) {
            self::RETURN   => 'إرجاع',
            self::EXCHANGE => 'استبدال',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $t) => [$t->value => $t->label()])
            ->all();
    }
}
