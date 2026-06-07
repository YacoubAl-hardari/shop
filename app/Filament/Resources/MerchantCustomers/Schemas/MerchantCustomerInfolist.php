<?php

namespace App\Filament\Resources\MerchantCustomers\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MerchantCustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('بيانات العميل')
                ->schema([
                    TextEntry::make('name')->label('الاسم'),
                    TextEntry::make('phone')->label('الهاتف'),
                    TextEntry::make('email')->label('البريد'),
                    TextEntry::make('balance')->label('المديونية')->money('SAR'),
                    TextEntry::make('credit_balance')->label('الرصيد الفائض')->money('SAR'),
                    IconEntry::make('is_active')->label('نشط')->boolean(),
                ]),
        ]);
    }
}
