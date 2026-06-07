<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupplierInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('بيانات المورد')
                ->schema([
                    TextEntry::make('name')->label('الاسم'),
                    TextEntry::make('phone')->label('الهاتف'),
                    TextEntry::make('email')->label('البريد'),
                    TextEntry::make('tax_number')->label('الرقم الضريبي'),
                    TextEntry::make('balance')->label('الرصيد')->money('SAR'),
                    IconEntry::make('is_active')->label('نشط')->boolean(),
                ]),
        ]);
    }
}
