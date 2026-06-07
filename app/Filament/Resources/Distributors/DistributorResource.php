<?php

namespace App\Filament\Resources\Distributors;

use App\Enums\UserType;
use App\Filament\Concerns\HasRoleAccess;
use App\Filament\Resources\Distributors\Pages\ListDistributors;
use App\Filament\Resources\Distributors\Schemas\DistributorForm;
use App\Filament\Resources\Distributors\Schemas\DistributorInfolist;
use App\Filament\Resources\Distributors\Tables\DistributorsTable;
use App\Models\Distributor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DistributorResource extends Resource
{
    use HasRoleAccess;

    protected static ?string $model = Distributor::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'name';

    protected static function allowedRoles(): array
    {
        return [UserType::MERCHANT, UserType::ADMIN];
    }

    public static function getNavigationLabel(): string
    {
        return 'الموزعون';
    }

    public static function getModelLabel(): string
    {
        return 'موزع';
    }

    public static function getPluralModelLabel(): string
    {
        return 'الموزعون';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'المشتريات';
    }

    public static function getNavigationParentItem(): ?string
    {
        return 'الموردون';
    }

    public static function form(Schema $schema): Schema
    {
        return DistributorForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DistributorInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DistributorsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDistributors::route('/'),
        ];
    }
}
