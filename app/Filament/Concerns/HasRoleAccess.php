<?php

namespace App\Filament\Concerns;

use App\Models\User;

trait HasRoleAccess
{
    abstract protected static function allowedRoles(): array;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->hasAnyRole(static::allowedRoles());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}
