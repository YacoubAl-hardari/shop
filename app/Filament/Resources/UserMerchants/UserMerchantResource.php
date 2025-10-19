<?php

namespace App\Filament\Resources\UserMerchants;

use App\Filament\Resources\UserMerchants\Pages\CreateUserMerchant;
use App\Filament\Resources\UserMerchants\Pages\EditUserMerchant;
use App\Filament\Resources\UserMerchants\Pages\ListUserMerchants;
use App\Filament\Resources\UserMerchants\Pages\ViewUserMerchant;
use App\Filament\Resources\UserMerchants\Schemas\UserMerchantForm;
use App\Filament\Resources\UserMerchants\Schemas\UserMerchantInfolist;
use App\Filament\Resources\UserMerchants\Tables\UserMerchantsTable;
use App\Models\UserMerchant;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserMerchantResource extends Resource
{
    protected static ?string $model = UserMerchant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationLabel = 'التجار';
    protected static ?int $navigationSort = 2;
    protected static ?string $pluralModelLabel = 'التجار';

    public static function getNavigationLabel(): string
    {
        return "التجار";
    }

    public static function form(Schema $schema): Schema
    {
        return UserMerchantForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserMerchantInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserMerchantsTable::configure($table);
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
            'index' => ListUserMerchants::route('/'),
            'create' => CreateUserMerchant::route('/create'),
            'view' => ViewUserMerchant::route('/{record}'),
            'edit' => EditUserMerchant::route('/{record}/edit'),
            'financial-stats' => \App\Filament\Resources\UserMerchants\Pages\MerchantFinancialStats::route('/{record}/financial-stats'),
        ];
    }
}
