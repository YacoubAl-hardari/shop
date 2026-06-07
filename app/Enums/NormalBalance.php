<?php

namespace App\Enums;

enum NormalBalance: string
{
    case DEBIT = 'debit';
    case CREDIT = 'credit';

    public function arabicLabel(): string
    {
        return match ($this) {
            self::DEBIT => 'مدين',
            self::CREDIT => 'دائن',
        };
    }
}
