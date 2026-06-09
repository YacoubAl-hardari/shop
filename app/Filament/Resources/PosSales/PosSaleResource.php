<?php

namespace App\Filament\Resources\PosSales;

use App\Enums\UserType;
use App\Filament\Concerns\HasRoleAccess;
use App\Filament\Resources\PosSales\Pages\ListPosSales;
use App\Filament\Resources\PosSales\Pages\ViewPosSale;
use App\Models\PosSale;
use BackedEnum;
use App\Filament\Resources\PosSales\Schemas\PosSaleForm;
use App\Filament\Resources\PosSales\Schemas\PosSaleInfolist;
use App\Filament\Resources\PosSales\Tables\PosSalesTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PosSaleResource extends Resource
{
    use HasRoleAccess;

    protected static ?string $model = PosSale::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static function allowedRoles(): array
    {
        return [UserType::MERCHANT, UserType::ADMIN];
    }

    public static function getNavigationLabel(): string { return 'فواتير البيع'; }
    public static function getModelLabel(): string { return 'فاتورة بيع'; }
    public static function getPluralModelLabel(): string { return 'فواتير البيع'; }
    public static function getNavigationGroup(): ?string { return 'المبيعات'; }
    public static function getNavigationSort(): ?int { return 2; }

    public static function form(Schema $schema): Schema
    {
        return PosSaleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PosSalesTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PosSaleInfolist::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosSales::route('/'),
            'view' => ViewPosSale::route('/{record}'),
        ];
    }
}
