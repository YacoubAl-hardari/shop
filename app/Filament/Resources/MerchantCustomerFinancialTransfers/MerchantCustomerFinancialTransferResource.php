<?php

namespace App\Filament\Resources\MerchantCustomerFinancialTransfers;

use App\Enums\UserType;
use App\Filament\Concerns\HasRoleAccess;
use App\Filament\Resources\MerchantCustomerFinancialTransfers\Pages\ListMerchantCustomerFinancialTransfers;
use App\Filament\Resources\MerchantCustomerFinancialTransfers\Pages\ViewMerchantCustomerFinancialTransfer;
use App\Filament\Resources\MerchantCustomerFinancialTransfers\Schemas\MerchantCustomerFinancialTransferInfolist;
use App\Filament\Resources\MerchantCustomerFinancialTransfers\Tables\MerchantCustomerFinancialTransfersTable;
use App\Models\MerchantCustomerFinancialTransfer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MerchantCustomerFinancialTransferResource extends Resource
{
    use HasRoleAccess;

    protected static ?string $model = MerchantCustomerFinancialTransfer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPathRoundedSquare;

    protected static ?string $recordTitleAttribute = 'id';

    protected static function allowedRoles(): array
    {
        return [UserType::MERCHANT, UserType::ADMIN];
    }

    public static function getNavigationLabel(): string
    {
        return 'التحويلات المالية';
    }

    public static function getModelLabel(): string
    {
        return 'تحويل مالي';
    }

    public static function getPluralModelLabel(): string
    {
        return 'التحويلات المالية للعملاء';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'المبيعات';
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->pending()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function infolist(Schema $schema): Schema
    {
        return MerchantCustomerFinancialTransferInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MerchantCustomerFinancialTransfersTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'merchantCustomer',
                'submitter',
                'reviewer',
                'paymentAccount',
                'statementShare',
                'merchantCustomerPayment',
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMerchantCustomerFinancialTransfers::route('/'),
            'view' => ViewMerchantCustomerFinancialTransfer::route('/{record}'),
        ];
    }
}
