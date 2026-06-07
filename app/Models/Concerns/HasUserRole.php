<?php

namespace App\Models\Concerns;

use App\Enums\UserType;

trait HasUserRole
{
    public function getUserType(): UserType
    {
        $role = $this->role;

        if ($role instanceof UserType) {
            return $role;
        }

        return UserType::from($role);
    }

    public function isAdmin(): bool
    {
        return $this->getUserType() === UserType::ADMIN;
    }

    public function isUser(): bool
    {
        return $this->getUserType() === UserType::USER;
    }

    public function isMerchant(): bool
    {
        return $this->getUserType() === UserType::MERCHANT;
    }

    public function hasAnyRole(array $roles): bool
    {
        $values = array_map(
            fn ($role) => $role instanceof UserType ? $role->value : $role,
            $roles,
        );

        return in_array($this->getUserType()->value, $values, true);
    }
}
