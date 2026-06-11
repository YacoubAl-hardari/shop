<?php

namespace App\Filament\Pages;

use App\Enums\UserType;
use App\Filament\Concerns\HasRoleAccess;
use App\Models\MerchantCustomerStatementShare;
use App\Services\CustomerStatementShareService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SharedCustomerStatements extends Page implements HasTable
{
    use HasRoleAccess;
    use InteractsWithTable;

    protected static function allowedRoles(): array
    {
        return [UserType::USER, UserType::ADMIN];
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'كشوف الحساب المشتركة';

    protected static ?string $title = 'كشوف الحساب المشتركة';

    protected static ?int $navigationSort = -4;

    protected string $view = 'filament.pages.shared-customer-statements';

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        $count = app(CustomerStatementShareService::class)->activeSharesForUser($user)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        app(CustomerStatementShareService::class)->reconcileActiveSharesForUser($user);
    }

    public function table(Table $table): Table
    {
        $user = Auth::user();

        return $table
            ->query(
                app(CustomerStatementShareService::class)
                    ->activeSharesQueryForUser($user)
                    ->with([
                        'team',
                        'merchantCustomer' => fn ($query) => $query->withoutGlobalScopes(),
                    ])
                    ->latest('shared_at'),
            )
            ->columns([
                TextColumn::make('team.name')
                    ->label('التاجر / الفرع'),
                TextColumn::make('merchantCustomer.name')
                    ->label('اسمك لدى التاجر'),
                TextColumn::make('merchantCustomer.balance')
                    ->label('المديونية'),
                TextColumn::make('merchantCustomer.credit_balance')
                    ->label('الرصيد الفائض'),
                TextColumn::make('shared_at')
                    ->label('تاريخ المشاركة')
                    ->dateTime('Y-m-d H:i'),
            ])
            ->recordActions([
                Action::make('viewStatement')
                    ->label('عرض كشف الحساب')
                    ->icon('heroicon-o-eye')
                    ->url(fn (MerchantCustomerStatementShare $record): string => ViewSharedCustomerStatement::statementUrl($record->uuid))
                    ->openUrlInNewTab(false),
            ])
            ->emptyStateHeading('لا توجد كشوف حساب مشتركة')
            ->emptyStateDescription('عندما يشاركك أحد التجار كشف حسابك سيظهر هنا.');
    }
}
