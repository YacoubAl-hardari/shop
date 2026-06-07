<?php

namespace App\Filament\Resources\MerchantPaymentAccounts;

use App\Enums\UserType;
use App\Filament\Concerns\HasRoleAccess;
use App\Filament\Resources\MerchantPaymentAccounts\Pages\ListMerchantPaymentAccounts;
use App\Filament\Resources\MerchantPaymentAccounts\Schemas\MerchantPaymentAccountForm;
use App\Filament\Resources\MerchantPaymentAccounts\Tables\MerchantPaymentAccountsTable;
use App\Models\MerchantPaymentAccount;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MerchantPaymentAccountResource extends Resource
{
    use HasRoleAccess;

    protected static ?string $model = MerchantPaymentAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $recordTitleAttribute = 'name';

    protected static function allowedRoles(): array
    {
        return [UserType::MERCHANT, UserType::ADMIN];
    }

    public static function getNavigationLabel(): string
    {
        return 'وسائل الدفع';
    }

    public static function getModelLabel(): string
    {
        return 'وسيلة دفع';
    }

    public static function getPluralModelLabel(): string
    {
        return 'البنوك والبطاقات';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'المحاسبة';
    }

    public static function getNavigationSort(): ?int
    {
        return 5;
    }

    public static function form(Schema $schema): Schema
    {
        return MerchantPaymentAccountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MerchantPaymentAccountsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMerchantPaymentAccounts::route('/'),
        ];
    }
}
