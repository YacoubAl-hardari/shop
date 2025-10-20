<?php

namespace App\Enums;

enum BudgetAlertType: string
{
    case WARNING = 'warning';
    case DANGER = 'danger';
    case EXCEEDED = 'exceeded';
    case INFO = 'info';

    public function getLabel(): string
    {
        return match ($this) {
            self::WARNING => 'تحذير',
            self::DANGER => 'خطر',
            self::EXCEEDED => 'تجاوز الحد',
            self::INFO => 'معلومة',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::WARNING => 'warning',
            self::DANGER => 'danger',
            self::EXCEEDED => 'danger',
            self::INFO => 'info',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::WARNING => 'heroicon-o-exclamation-triangle',
            self::DANGER => 'heroicon-o-shield-exclamation',
            self::EXCEEDED => 'heroicon-o-x-circle',
            self::INFO => 'heroicon-o-information-circle',
        };
    }

    public static function toArray(): array
    {
        return [
            self::WARNING->value => self::WARNING->getLabel(),
            self::DANGER->value => self::DANGER->getLabel(),
            self::EXCEEDED->value => self::EXCEEDED->getLabel(),
            self::INFO->value => self::INFO->getLabel(),
        ];
    }
}


