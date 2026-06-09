<?php

namespace App\Filament\Resources\PosSaleReturns;

use App\Enums\UserType;
use App\Filament\Concerns\HasRoleAccess;
use App\Filament\Resources\PosSaleReturns\Schemas\PosSaleReturnForm;
use App\Filament\Resources\PosSaleReturns\Tables\PosSaleReturnsTable;
use App\Models\PosSaleReturn;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PosSaleReturnResource extends Resource
{
    use HasRoleAccess;

    protected static ?string $model = PosSaleReturn::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUturnLeft;

    protected static function allowedRoles(): array
    {
        return [UserType::MERCHANT, UserType::ADMIN];
    }

    public static function getNavigationLabel(): string { return 'المرتجعات والاستبدال'; }
    public static function getModelLabel(): string { return 'مرتجع / استبدال'; }
    public static function getPluralModelLabel(): string { return 'المرتجعات والاستبدال'; }
    public static function getNavigationGroup(): ?string { return 'المبيعات'; }
    public static function getNavigationSort(): ?int { return 3; }

    public static function form(Schema $schema): Schema
    {
        return PosSaleReturnForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PosSaleReturnsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPosSaleReturns::route('/'),
            'create' => Pages\ProcessPosSaleReturn::route('/process'),
            'view'   => Pages\ViewPosSaleReturn::route('/{record}'),
        ];
    }
}
