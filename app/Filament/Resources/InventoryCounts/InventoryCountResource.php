<?php

namespace App\Filament\Resources\InventoryCounts;

use App\Enums\UserType;
use App\Filament\Concerns\HasRoleAccess;
use App\Filament\Resources\InventoryCounts\Schemas\InventoryCountForm;
use App\Filament\Resources\InventoryCounts\Tables\InventoryCountsTable;
use App\Models\InventoryCount;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InventoryCountResource extends Resource
{
    use HasRoleAccess;

    protected static ?string $model = InventoryCount::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static function allowedRoles(): array
    {
        return [UserType::MERCHANT, UserType::ADMIN];
    }

    public static function getNavigationLabel(): string { return 'الجرد السنوي'; }
    public static function getModelLabel(): string { return 'جرد'; }
    public static function getPluralModelLabel(): string { return 'الجرد السنوي'; }
    public static function getNavigationGroup(): ?string { return 'المخزون'; }
    public static function getNavigationSort(): ?int { return 3; }

    public static function form(Schema $schema): Schema
    {
        return InventoryCountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InventoryCountsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInventoryCounts::route('/'),
            'create' => Pages\CreateInventoryCount::route('/create'),
            'view'   => Pages\ViewInventoryCount::route('/{record}'),
        ];
    }
}
