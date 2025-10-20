<?php

namespace App\Filament\Resources\BudgetCategories;

use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\BudgetCategory;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\BudgetCategories\Pages\EditBudgetCategory;
use App\Filament\Resources\BudgetCategories\Pages\ViewBudgetCategory;
use App\Filament\Resources\BudgetCategories\Pages\CreateBudgetCategory;
use App\Filament\Resources\BudgetCategories\Pages\ListBudgetCategories;
use App\Filament\Resources\BudgetCategories\Schemas\BudgetCategoryForm;
use App\Filament\Resources\BudgetCategories\Tables\BudgetCategoriesTable;
use App\Filament\Resources\BudgetCategories\Schemas\BudgetCategoryInfolist;

class BudgetCategoryResource extends Resource
{
    protected static ?string $model = BudgetCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    public static function getNavigationLabel(): string
    {
        return 'فئات الميزانية';
    }

    public static function getModelLabel(): string
    {
        return 'فئة';
    }

    public static function getPluralModelLabel(): string
    {
        return 'فئات الميزانية';
    }

    public static function getNavigationSort(): ?int
    {
        return 11;
    }

    public static function getNavigationGroup(): ?string
    {
        return 'الميزانية الشخصية';
    }

    public static function form(Schema $schema): Schema
    {
        return BudgetCategoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BudgetCategoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BudgetCategoriesTable::configure($table);
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
            'index' => ListBudgetCategories::route('/'),
            'create' => CreateBudgetCategory::route('/create'),
            'view' => ViewBudgetCategory::route('/{record}'),
            'edit' => EditBudgetCategory::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::id())
            ->orderBy('sort_order');
    }
}

