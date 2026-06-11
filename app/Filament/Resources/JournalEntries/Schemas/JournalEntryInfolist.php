<?php

namespace App\Filament\Resources\JournalEntries\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JournalEntryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('بيانات القيد')
                ->schema([
                    TextEntry::make('entry_number')->label('رقم القيد'),
                    TextEntry::make('entry_date')->label('التاريخ')->date(),
                    TextEntry::make('description')->label('الوصف'),
                    TextEntry::make('status')->label('الحالة')->formatStateUsing(fn($state) => $state?->arabicLabel()),
                    TextEntry::make('creator.name')->label('أنشئ بواسطة'),
                    TextEntry::make('posted_at')->label('تاريخ الترحيل')->dateTime(),
                ]),

            Section::make('سطور القيد')
                ->schema([
                    RepeatableEntry::make('lines')
                        ->label('')
                        ->schema([
                            TextEntry::make('account.code')->label('الرمز'),
                            TextEntry::make('account.name')->label('الحساب'),
                            TextEntry::make('debit_amount')->label('مدين'),
                            TextEntry::make('credit_amount')->label('دائن'),
                            TextEntry::make('description')->label('الوصف'),
                        ])
                        ->columns(5),
                ]),
        ]);
    }
}
