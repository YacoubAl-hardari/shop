<?php

namespace App\Filament\Resources\UserMerchantAccountStatements;

use App\Filament\Resources\UserMerchantAccountStatements\Pages\CreateUserMerchantAccountStatement;
use App\Filament\Resources\UserMerchantAccountStatements\Pages\EditUserMerchantAccountStatement;
use App\Filament\Resources\UserMerchantAccountStatements\Pages\ListUserMerchantAccountStatements;
use App\Filament\Resources\UserMerchantAccountStatements\Pages\ViewUserMerchantAccountStatement;
use App\Filament\Resources\UserMerchantAccountStatements\Schemas\UserMerchantAccountStatementForm;
use App\Filament\Resources\UserMerchantAccountStatements\Schemas\UserMerchantAccountStatementInfolist;
use App\Filament\Resources\UserMerchantAccountStatements\Tables\UserMerchantAccountStatementsTable;
use App\Models\UserMerchantAccountStatement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserMerchantAccountStatementResource extends Resource
{
    protected static ?string $model = UserMerchantAccountStatement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $recordTitleAttribute = 'id';

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
        return "كشوفات حسابات التجار";
    }
    public static function getNavigationLabel(): string
    {
        return "كشوفات حسابات التجار";
    }

    public static function getPluralModelLabel(): string
    {
        return "كشوفات حسابات التجار";
    }

    public static function form(Schema $schema): Schema
    {
        return UserMerchantAccountStatementForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserMerchantAccountStatementInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserMerchantAccountStatementsTable::configure($table);
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
            'index' => ListUserMerchantAccountStatements::route('/'),
            'create' => CreateUserMerchantAccountStatement::route('/create'),
            'view' => ViewUserMerchantAccountStatement::route('/{record}'),
            'edit' => EditUserMerchantAccountStatement::route('/{record}/edit'),
        ];
    }
}
