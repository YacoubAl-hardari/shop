<?php

namespace App\Filament\Resources\MerchantProducts;

use App\Enums\UserType;
use App\Filament\Concerns\HasRoleAccess;
use App\Filament\Resources\MerchantProducts\Pages\ListMerchantProducts;
use App\Filament\Resources\MerchantProducts\Schemas\MerchantProductForm;
use App\Filament\Resources\MerchantProducts\Schemas\MerchantProductInfolist;
use App\Filament\Resources\MerchantProducts\Tables\MerchantProductsTable;
use App\Models\MerchantProduct;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MerchantProductResource extends Resource
{
    use HasRoleAccess;

    protected static ?string $model = MerchantProduct::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $recordTitleAttribute = 'name';

    protected static function allowedRoles(): array
    {
        return [UserType::MERCHANT, UserType::ADMIN];
    }

    public static function getNavigationLabel(): string
    {
        return 'المنتجات';
    }

    public static function getModelLabel(): string
    {
        return 'منتج';
    }

    public static function getPluralModelLabel(): string
    {
        return 'المنتجات';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'المخزون';
    }

    public static function form(Schema $schema): Schema
    {
        return MerchantProductForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MerchantProductInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MerchantProductsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMerchantProducts::route('/'),
        ];
    }
}
