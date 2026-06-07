<?php

namespace App\Filament\Pages;

use App\Enums\UserType;
use App\Filament\Concerns\HasRoleAccess;
use App\Models\JournalLine;
use App\Models\MerchantCustomer;
use App\Models\MerchantCustomerStatementShare;
use App\Services\CustomerStatementShareService;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class ViewSharedCustomerStatement extends Page
{
    use HasRoleAccess;

    protected static function allowedRoles(): array
    {
        return [UserType::USER, UserType::ADMIN];
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentMagnifyingGlass;

    protected static ?string $title = 'كشف الحساب المشترك';

    protected static ?string $slug = 'shared-statement/{token}';

    protected string $view = 'filament.pages.view-shared-customer-statement';

    public ?string $statementRef = null;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function routes(Panel $panel, ?\Filament\Pages\PageConfiguration $configuration = null): void
    {
        $route = Route::get(static::getRoutePath($panel), static::class)
            ->whereUuid('token')
            ->middleware(static::getRouteMiddleware($panel))
            ->withoutMiddleware(static::getWithoutRouteMiddleware($panel))
            ->name(static::getRelativeRouteName($panel));

        if ($panel->hasTenancy() && blank($panel->getTenantDomain()) && (static::getRoutePath($panel) === '/')) {
            $route->fallback();
        }
    }

    public static function statementUrl(string $uuid, ?Model $tenant = null): string
    {
        return static::getUrl(
            ['token' => $uuid],
            tenant: $tenant ?? Filament::getTenant(),
        );
    }

    public function mount(string $token): void
    {
        $this->statementRef = $token;

        $user = Auth::user();

        if (! $user) {
            return;
        }

        $service = app(CustomerStatementShareService::class);
        $service->reconcileActiveSharesForUser($user);

        if (! $service->findViewableShareByUuid($token, $user)) {
            Notification::make()
                ->title('كشف الحساب غير متاح')
                ->body('لم تعد هذه المشاركة نشطة أو انتهت صلاحيتها.')
                ->warning()
                ->send();

            $this->redirect(SharedCustomerStatements::getUrl());
        }
    }

    public function getStatementShare(): ?MerchantCustomerStatementShare
    {
        if ($this->statementRef === null) {
            return null;
        }

        $user = Auth::user();

        if (! $user) {
            return null;
        }

        return app(CustomerStatementShareService::class)
            ->findViewableShareByUuid($this->statementRef, $user);
    }

    public function getSharedCustomer(): ?MerchantCustomer
    {
        $share = $this->getStatementShare();

        if ($share === null) {
            return null;
        }

        return app(CustomerStatementShareService::class)->resolveSharedCustomer($share);
    }

    public function getTitle(): string
    {
        $customer = $this->getSharedCustomer();

        if ($customer !== null) {
            return 'كشف حساب: '.$customer->name;
        }

        return static::$title ?? 'كشف الحساب المشترك';
    }

    public function getStatementLines()
    {
        $share = $this->getStatementShare();
        $customer = $this->getSharedCustomer();

        if ($share === null || $customer === null) {
            return collect();
        }

        return JournalLine::query()
            ->select('journal_lines.*')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_entries.team_id', $share->team_id)
            ->where('journal_lines.subledger_type', MerchantCustomer::class)
            ->where('journal_lines.subledger_id', $customer->id)
            ->with([
                'journalEntry' => fn ($query) => $query->withoutGlobalScopes(),
                'account' => fn ($query) => $query->withoutGlobalScopes(),
            ])
            ->orderByDesc('journal_lines.created_at')
            ->get();
    }
}
