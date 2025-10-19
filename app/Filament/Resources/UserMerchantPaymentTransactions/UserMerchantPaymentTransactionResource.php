<?php

namespace App\Filament\Resources\UserMerchantPaymentTransactions;

use App\Filament\Resources\UserMerchantPaymentTransactions\Pages\CreateUserMerchantPaymentTransaction;
use App\Filament\Resources\UserMerchantPaymentTransactions\Pages\EditUserMerchantPaymentTransaction;
use App\Filament\Resources\UserMerchantPaymentTransactions\Pages\ListUserMerchantPaymentTransactions;
use App\Filament\Resources\UserMerchantPaymentTransactions\Pages\ViewUserMerchantPaymentTransaction;
use App\Filament\Resources\UserMerchantPaymentTransactions\Schemas\UserMerchantPaymentTransactionForm;
use App\Filament\Resources\UserMerchantPaymentTransactions\Schemas\UserMerchantPaymentTransactionInfolist;
use App\Filament\Resources\UserMerchantPaymentTransactions\Tables\UserMerchantPaymentTransactionsTable;
use App\Models\UserMerchantPaymentTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserMerchantPaymentTransactionResource extends Resource
{
    protected static ?string $model = UserMerchantPaymentTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $recordTitleAttribute = 'transaction_number';
    protected static ?string $navigationLabel = 'معاملات دفع التجار';
    protected static ?int $navigationSort = 11;
    protected static ?string $pluralModelLabel = 'معاملات دفع التجار';

    public static function getNavigationLabel(): string
    {
        return "معاملات دفع التجار";
    }

    public static function form(Schema $schema): Schema
    {
        return UserMerchantPaymentTransactionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserMerchantPaymentTransactionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserMerchantPaymentTransactionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUserMerchantPaymentTransactions::route('/'),
            'create' => CreateUserMerchantPaymentTransaction::route('/create'),
            'view' => ViewUserMerchantPaymentTransaction::route('/{record}'),
            'edit' => EditUserMerchantPaymentTransaction::route('/{record}/edit'),
        ];
    }
}
