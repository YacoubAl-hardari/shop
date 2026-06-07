<?php

namespace App\Filament\Resources\MerchantCustomers;

use App\Enums\UserType;
use App\Filament\Concerns\HasRoleAccess;
use App\Filament\Resources\MerchantCustomers\Pages\ListMerchantCustomers;
use App\Filament\Resources\MerchantCustomers\Pages\MerchantCustomerStatement;
use App\Filament\Resources\MerchantCustomers\Pages\ViewMerchantCustomer;
use App\Filament\Resources\MerchantCustomers\Schemas\MerchantCustomerForm;
use App\Filament\Resources\MerchantCustomers\Schemas\MerchantCustomerInfolist;
use App\Filament\Resources\MerchantCustomers\Tables\MerchantCustomersTable;
use App\Models\MerchantCustomer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MerchantCustomerResource extends Resource
{
    use HasRoleAccess;

    protected static ?string $model = MerchantCustomer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'name';

    protected static function allowedRoles(): array
    {
        return [UserType::MERCHANT, UserType::ADMIN];
    }

    public static function getNavigationLabel(): string
    {
        return 'العملاء';
    }

    public static function getModelLabel(): string
    {
        return 'عميل';
    }

    public static function getPluralModelLabel(): string
    {
        return 'العملاء';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'المبيعات';
    }

    public static function form(Schema $schema): Schema
    {
        return MerchantCustomerForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MerchantCustomerInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MerchantCustomersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMerchantCustomers::route('/'),
            'view' => ViewMerchantCustomer::route('/{record}'),
            'statement' => MerchantCustomerStatement::route('/{record}/statement'),
        ];
    }
}
