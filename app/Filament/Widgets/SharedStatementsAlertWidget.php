<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\SharedCustomerStatements;
use App\Filament\Pages\ViewSharedCustomerStatement;
use App\Services\CustomerStatementShareService;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class SharedStatementsAlertWidget extends Widget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = -10;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.shared-statements-alert';

    public function getShares()
    {
        $user = Auth::user();

        if (! $user) {
            return collect();
        }

        return app(CustomerStatementShareService::class)->activeSharesForUser($user);
    }

    public static function canView(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->isUser()) {
            return false;
        }

        return app(CustomerStatementShareService::class)->userHasActiveShares($user);
    }

    public function listUrl(): string
    {
        return SharedCustomerStatements::getUrl();
    }

    public function viewUrl(string $shareUuid): string
    {
        return ViewSharedCustomerStatement::statementUrl($shareUuid);
    }
}
