<?php

namespace App\Filament\Resources\UserMerchantWallets\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;

class UserMerchantWalletInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('معلومات المحفظة')
                    ->schema([
                        TextEntry::make('userMerchant.name')
                            ->label('التاجر'),

                        TextEntry::make('account_name')
                            ->label('اسم الحساب'),

                        TextEntry::make('bank_account_number')
                            ->label('رقم الحساب المصرفي'),

                        TextEntry::make('bank_name')
                            ->label('اسم البنك'),

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
