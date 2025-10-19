<?php

namespace App\Filament\Resources\UserMerchants\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;

class UserMerchantInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('معلومات التاجر')
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('المستخدم'),

                        TextEntry::make('name')
                            ->label('اسم التاجر'),

                        TextEntry::make('email')
                            ->label('البريد الإلكتروني'),

                        TextEntry::make('phone')
                            ->label('رقم الهاتف'),

                        TextEntry::make('information')
                            ->label('معلومات إضافية')
                            ->columnSpanFull(),

                        IconEntry::make('is_active')
                            ->label('الحالة')
                            ->boolean(),

                        TextEntry::make('balance')
                            ->label('الرصيد')
                            ->money('USD'),

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
