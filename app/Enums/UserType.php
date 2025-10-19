<?php

namespace App\Enums;

enum UserType: string
{
    case ADMIN = 'admin';
    case USER = 'user';

    /**
     * Get all enum values as an array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get the default user type
     */
    public static function default(): self
    {
        return self::USER;
    }

    /**
     * Get the label for the user type
     */
    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrator',
            self::USER => 'User',
        };
    }

    /**
     * Get the Arabic label for the user type
     */
    public function arabicLabel(): string
    {
        return match($this) {
            self::ADMIN => 'مدير',
            self::USER => 'مستخدم',
        };
    }

    /**
     * Check if the user type is admin
     */
    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    /**
     * Check if the user type is regular user
     */
    public function isUser(): bool
    {
        return $this === self::USER;
    }

    /**
     * Get user type by value
     */
    public static function fromValue(string $value): ?self
    {
        return self::tryFrom($value);
    }

    /**
     * Get all user types as key-value pairs for forms
     */
    public static function options(): array
    {
        return [
            self::ADMIN->value => self::ADMIN->label(),
            self::USER->value => self::USER->label(),
        ];
    }

    /**
     * Get all user types as key-value pairs with Arabic labels
     */
    public static function arabicOptions(): array
    {
        return [
            self::ADMIN->value => self::ADMIN->arabicLabel(),
            self::USER->value => self::USER->arabicLabel(),
        ];
    }
}
