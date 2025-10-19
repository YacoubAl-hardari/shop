<?php

namespace App\Filament\Resources\UserMerchantOrders;

use App\Filament\Resources\UserMerchantOrders\Pages\CreateUserMerchantOrder;
use App\Filament\Resources\UserMerchantOrders\Pages\EditUserMerchantOrder;
use App\Filament\Resources\UserMerchantOrders\Pages\ListUserMerchantOrders;
use App\Filament\Resources\UserMerchantOrders\Pages\ViewUserMerchantOrder;
use App\Filament\Resources\UserMerchantOrders\Schemas\UserMerchantOrderForm;
use App\Filament\Resources\UserMerchantOrders\Schemas\UserMerchantOrderInfolist;
use App\Filament\Resources\UserMerchantOrders\Tables\UserMerchantOrdersTable;
use App\Models\UserMerchantOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserMerchantOrderResource extends Resource
{
    protected static ?string $model = UserMerchantOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static ?string $recordTitleAttribute = 'order_number';
 

    public static function getModelLabel(): string
    {
        return "الطلبات";
    }
    public static function getNavigationLabel(): string
    {
        return "الطلبات";
    }

    public static function getPluralModelLabel(): string
    {
        return "الطلبات";
    }

    public static function form(Schema $schema): Schema
    {
        return UserMerchantOrderForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserMerchantOrderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserMerchantOrdersTable::configure($table);
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
            'index' => ListUserMerchantOrders::route('/'),
            'create' => CreateUserMerchantOrder::route('/create'),
            'view' => ViewUserMerchantOrder::route('/{record}'),
            'edit' => EditUserMerchantOrder::route('/{record}/edit'),
        ];
    }
}
