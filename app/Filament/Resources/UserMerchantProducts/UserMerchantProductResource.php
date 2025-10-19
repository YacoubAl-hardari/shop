<?php

namespace App\Filament\Resources\UserMerchantProducts;

use App\Filament\Resources\UserMerchantProducts\Pages\CreateUserMerchantProduct;
use App\Filament\Resources\UserMerchantProducts\Pages\EditUserMerchantProduct;
use App\Filament\Resources\UserMerchantProducts\Pages\ListUserMerchantProducts;
use App\Filament\Resources\UserMerchantProducts\Pages\ViewUserMerchantProduct;
use App\Filament\Resources\UserMerchantProducts\Schemas\UserMerchantProductForm;
use App\Filament\Resources\UserMerchantProducts\Schemas\UserMerchantProductInfolist;
use App\Filament\Resources\UserMerchantProducts\Tables\UserMerchantProductsTable;
use App\Models\UserMerchantProduct;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserMerchantProductResource extends Resource
{
    protected static ?string $model = UserMerchantProduct::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    public static function getNavigationParentItem(): ?string
    {
        return "التجار";
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function getModelLabel(): string
    {
        return "منتجات التجار";
    }
    public static function getNavigationLabel(): string
    {
        return "منتجات التجار";
    }

   
    public static function getPluralModelLabel(): string
    {
        return "منتجات التجار";
    }

    public static function form(Schema $schema): Schema
    {
        return UserMerchantProductForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserMerchantProductInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserMerchantProductsTable::configure($table);
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
            'index' => ListUserMerchantProducts::route('/'),
            // 'create' => CreateUserMerchantProduct::route('/create'),
            // 'view' => ViewUserMerchantProduct::route('/{record}'),
            // 'edit' => EditUserMerchantProduct::route('/{record}/edit'),
        ];
    }
}
