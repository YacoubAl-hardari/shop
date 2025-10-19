<?php

namespace App\Filament\Resources\UserMerchantOrders\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class UserMerchantOrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('معلومات الطلب')
                    ->schema([
                        TextEntry::make('order_number')
                            ->label('رقم الطلب'),

                        TextEntry::make('userMerchant.name')
                            ->label('التاجر'),

                        TextEntry::make('user.name')
                            ->label('المستخدم'),

                        TextEntry::make('total_price')
                            ->label('إجمالي السعر')
                            ->money('USD'),

                        TextEntry::make('note')
                            ->label('ملاحظات')
                            ->columnSpanFull(),

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
