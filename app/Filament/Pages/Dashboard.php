<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function mount(): void
    {
        $user = auth()->user();

        if ($user instanceof User && $user->isMerchant() && ! $user->isAdmin()) {
            $this->redirect(MerchantStatisticsDashboard::getUrl(), navigate: true);
        }
    }

    public static function canAccess(): bool
    {
        return auth()->user() instanceof User;
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        return $user->isUser() || $user->isAdmin();
    }

    /**
     * @return array<class-string>
     */
    public function getWidgets(): array
    {
        $user = auth()->user();

        if ($user instanceof User && $user->isMerchant() && ! $user->isAdmin()) {
            return [];
        }

        return parent::getWidgets();
    }
}
