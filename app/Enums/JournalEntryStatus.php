<?php

namespace App\Enums;

enum JournalEntryStatus: string
{
    case DRAFT = 'draft';
    case POSTED = 'posted';
    case VOID = 'void';

    public function arabicLabel(): string
    {
        return match ($this) {
            self::DRAFT => 'مسودة',
            self::POSTED => 'مرحّل',
            self::VOID => 'ملغي',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status) => [$status->value => $status->arabicLabel()])
            ->all();
    }
}
