<?php

namespace App\Enums;

enum UserType: string
{
    case ADMIN = 'admin';
    case USER = 'user';
    case MERCHANT = 'merchant';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function default(): self
    {
        return self::USER;
    }

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::USER => 'User',
            self::MERCHANT => 'Merchant',
        };
    }

    public function arabicLabel(): string
    {
        return match ($this) {
            self::ADMIN => 'مدير',
            self::USER => 'مستخدم',
            self::MERCHANT => 'تاجر',
        };
    }

    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    public function isUser(): bool
    {
        return $this === self::USER;
    }

    public function isMerchant(): bool
    {
        return $this === self::MERCHANT;
    }

    public static function fromValue(string $value): ?self
    {
        return self::tryFrom($value);
    }

    public static function options(): array
    {
        return [
            self::ADMIN->value => self::ADMIN->label(),
            self::USER->value => self::USER->label(),
            self::MERCHANT->value => self::MERCHANT->label(),
        ];
    }

    public static function arabicOptions(): array
    {
        return [
            self::ADMIN->value => self::ADMIN->arabicLabel(),
            self::USER->value => self::USER->arabicLabel(),
            self::MERCHANT->value => self::MERCHANT->arabicLabel(),
        ];
    }

    public static function registrationOptions(): array
    {
        return [
            self::USER->value => self::USER->arabicLabel(),
            self::MERCHANT->value => self::MERCHANT->arabicLabel(),
        ];
    }
}
