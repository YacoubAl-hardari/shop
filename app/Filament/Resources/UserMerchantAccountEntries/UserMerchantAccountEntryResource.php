<?php

namespace App\Filament\Resources\UserMerchantAccountEntries;

use App\Filament\Resources\UserMerchantAccountEntries\Pages\CreateUserMerchantAccountEntry;
use App\Filament\Resources\UserMerchantAccountEntries\Pages\EditUserMerchantAccountEntry;
use App\Filament\Resources\UserMerchantAccountEntries\Pages\ListUserMerchantAccountEntries;
use App\Filament\Resources\UserMerchantAccountEntries\Pages\ViewUserMerchantAccountEntry;
use App\Filament\Resources\UserMerchantAccountEntries\Schemas\UserMerchantAccountEntryForm;
use App\Filament\Resources\UserMerchantAccountEntries\Schemas\UserMerchantAccountEntryInfolist;
use App\Filament\Resources\UserMerchantAccountEntries\Tables\UserMerchantAccountEntriesTable;
use App\Models\UserMerchantAccountEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserMerchantAccountEntryResource extends Resource
{
    protected static ?string $model = UserMerchantAccountEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $recordTitleAttribute = 'entry_number';
    protected static ?string $navigationLabel = 'قيود حسابات التجار';
    protected static ?int $navigationSort = 10;
    protected static ?string $pluralModelLabel = 'قيود حسابات التجار';

    public static function getNavigationLabel(): string
    {
        return "قيود حسابات التجار";
    }

    public static function form(Schema $schema): Schema
    {
        return UserMerchantAccountEntryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserMerchantAccountEntryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserMerchantAccountEntriesTable::configure($table);
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
            'index' => ListUserMerchantAccountEntries::route('/'),
            'create' => CreateUserMerchantAccountEntry::route('/create'),
            'view' => ViewUserMerchantAccountEntry::route('/{record}'),
            'edit' => EditUserMerchantAccountEntry::route('/{record}/edit'),
        ];
    }
}
