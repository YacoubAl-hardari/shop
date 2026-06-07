<?php

namespace App\Filament\Resources\Distributors\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DistributorInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('بيانات الموزع')
                ->schema([
                    TextEntry::make('supplier.name')->label('المورد'),
                    TextEntry::make('name')->label('الاسم'),
                    TextEntry::make('phone')->label('الهاتف'),
                    TextEntry::make('contact_info')->label('معلومات التواصل'),
                    IconEntry::make('is_active')->label('نشط')->boolean(),
                ]),
        ]);
    }
}
