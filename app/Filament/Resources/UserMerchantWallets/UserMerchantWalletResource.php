<?php

namespace App\Filament\Resources\UserMerchantWallets;

use App\Filament\Resources\UserMerchantWallets\Pages\CreateUserMerchantWallet;
use App\Filament\Resources\UserMerchantWallets\Pages\EditUserMerchantWallet;
use App\Filament\Resources\UserMerchantWallets\Pages\ListUserMerchantWallets;
use App\Filament\Resources\UserMerchantWallets\Pages\ViewUserMerchantWallet;
use App\Filament\Resources\UserMerchantWallets\Schemas\UserMerchantWalletForm;
use App\Filament\Resources\UserMerchantWallets\Schemas\UserMerchantWalletInfolist;
use App\Filament\Resources\UserMerchantWallets\Tables\UserMerchantWalletsTable;
use App\Models\UserMerchantWallet;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserMerchantWalletResource extends Resource
{
    protected static ?string $model = UserMerchantWallet::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $recordTitleAttribute = 'account_name';
    protected static ?string $navigationLabel = 'محافظ التجار';
    protected static ?int $navigationSort = 6;
    protected static ?string $pluralModelLabel = 'محافظ التجار';

    public static function getNavigationLabel(): string
    {
        return "محافظ التجار";
    }

    public static function form(Schema $schema): Schema
    {
        return UserMerchantWalletForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserMerchantWalletInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserMerchantWalletsTable::configure($table);
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
            'index' => ListUserMerchantWallets::route('/'),
            // 'create' => CreateUserMerchantWallet::route('/create'),
            // 'view' => ViewUserMerchantWallet::route('/{record}'),
            // 'edit' => EditUserMerchantWallet::route('/{record}/edit'),
        ];
    }
}
