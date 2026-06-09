<?php

namespace App\Filament\Resources\StockMovements;

use App\Enums\UserType;
use App\Filament\Concerns\HasRoleAccess;
use App\Filament\Resources\StockMovements\Schemas\StockMovementForm;
use App\Filament\Resources\StockMovements\Tables\StockMovementsTable;
use App\Models\StockMovement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StockMovementResource extends Resource
{
    use HasRoleAccess;

    protected static ?string $model = StockMovement::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static function allowedRoles(): array
    {
        return [UserType::MERCHANT, UserType::ADMIN];
    }

    public static function getNavigationLabel(): string { return 'حركات المخزون'; }
    public static function getModelLabel(): string { return 'حركة مخزون'; }
    public static function getPluralModelLabel(): string { return 'حركات المخزون'; }
    public static function getNavigationGroup(): ?string { return 'المخزون'; }
    public static function getNavigationSort(): ?int { return 2; }

    public static function form(Schema $schema): Schema
    {
        return StockMovementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockMovementsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockMovements::route('/'),
        ];
    }
}
