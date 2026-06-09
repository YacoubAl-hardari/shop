<?php

namespace App\Enums;

enum InventoryCountStatus: string
{
    case DRAFT       = 'draft';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED   = 'completed';
    case APPROVED    = 'approved';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT       => 'مسودة',
            self::IN_PROGRESS => 'قيد الجرد',
            self::COMPLETED   => 'مكتمل',
            self::APPROVED    => 'معتمد',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT       => 'gray',
            self::IN_PROGRESS => 'warning',
            self::COMPLETED   => 'info',
            self::APPROVED    => 'success',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $s) => [$s->value => $s->label()])
            ->all();
    }
}
