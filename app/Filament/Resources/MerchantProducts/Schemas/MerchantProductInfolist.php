<?php

namespace App\Filament\Resources\MerchantProducts\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MerchantProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('بيانات المنتج')
                ->schema([
                    TextEntry::make('name')->label('الاسم'),
                    TextEntry::make('barcode')->label('الباركود'),
                    TextEntry::make('sku')->label('الرمز الداخلي'),
                    TextEntry::make('supplier.name')->label('المورد')->placeholder('—'),
                    TextEntry::make('distributor.name')->label('الموزع')->placeholder('—'),
                    TextEntry::make('price')->label('السعر')->money('SAR'),
                    TextEntry::make('cost')->label('التكلفة')->money('SAR'),
                    TextEntry::make('stock_quantity')->label('الكمية'),
                    TextEntry::make('unit')->label('الوحدة'),
                    IconEntry::make('is_active')->label('نشط')->boolean(),
                ])
                ->columns(3)
                ->columnSpanFull()
                ,
        ]);
    }
}
