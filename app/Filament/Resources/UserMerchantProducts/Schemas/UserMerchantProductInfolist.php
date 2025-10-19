<?php

namespace App\Filament\Resources\UserMerchantProducts\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;

class UserMerchantProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('معلومات المنتج')
                    ->schema([
                        TextEntry::make('userMerchant.name')
                            ->label('التاجر'),

                        TextEntry::make('name')
                            ->label('اسم المنتج'),

                        TextEntry::make('price')
                            ->label('السعر')
                            ->money('USD'),

                        TextEntry::make('barcode')
                            ->label('الباركود'),

                        TextEntry::make('brand')
                            ->label('العلامة التجارية'),

                        TextEntry::make('description')
                            ->label('الوصف')
                            ->columnSpanFull(),

                        ImageEntry::make('image')
                            ->label('صورة المنتج')
                            ->height(200),

                        IconEntry::make('is_active')
                            ->label('الحالة')
                            ->boolean(),

                        TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->dateTime(),

                        TextEntry::make('updated_at')
                            ->label('تاريخ التحديث')
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }
}
