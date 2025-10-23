<?php

namespace App\Filament\Resources\MerchantCategories;

use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\MerchantCategory;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\MerchantCategories\Pages\EditMerchantCategory;
use App\Filament\Resources\MerchantCategories\Pages\ViewMerchantCategory;
use App\Filament\Resources\MerchantCategories\Pages\CreateMerchantCategory;
use App\Filament\Resources\MerchantCategories\Pages\ListMerchantCategories;
use App\Filament\Resources\MerchantCategories\Schemas\MerchantCategoryForm;
use App\Filament\Resources\MerchantCategories\Tables\MerchantCategoriesTable;
use App\Filament\Resources\MerchantCategories\Schemas\MerchantCategoryInfolist;

class MerchantCategoryResource extends Resource
{
    protected static ?string $model = MerchantCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    public static function getNavigationLabel(): string
    {
        return 'تصنيفات التاجر';
    }

    public static function getModelLabel(): string
    {
        return 'تصنيف';
    }

    public static function getPluralModelLabel(): string
    {
        return 'تصنيفات التاجر';
    }

    public static function getNavigationSort(): ?int
    {
        return 5;
    }


    public static function form(Schema $schema): Schema
    {
        return MerchantCategoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MerchantCategoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MerchantCategoriesTable::configure($table);
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
            'index' => ListMerchantCategories::route('/'),
            'create' => CreateMerchantCategory::route('/create'),
            'view' => ViewMerchantCategory::route('/{record}'),
            'edit' => EditMerchantCategory::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('team_id', Auth::user()->teams()->first()->id)
            ->orderBy('name');
    }
}
