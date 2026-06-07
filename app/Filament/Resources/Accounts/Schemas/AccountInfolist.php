<?php

namespace App\Filament\Resources\Accounts\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AccountInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('بيانات الحساب')
                ->schema([
                    TextEntry::make('code')->label('الرمز'),
                    TextEntry::make('name')->label('الاسم'),
                    TextEntry::make('type')->label('النوع')->formatStateUsing(fn ($state) => $state?->arabicLabel()),
                    TextEntry::make('normal_balance')->label('الرصيد الطبيعي')->formatStateUsing(fn ($state) => $state?->arabicLabel()),
                    TextEntry::make('parent.name')->label('الحساب الأب'),
                    IconEntry::make('is_system')->label('حساب نظام')->boolean(),
                    IconEntry::make('is_active')->label('نشط')->boolean(),
                    TextEntry::make('description')->label('الوصف'),
                ]),
        ]);
    }
}
