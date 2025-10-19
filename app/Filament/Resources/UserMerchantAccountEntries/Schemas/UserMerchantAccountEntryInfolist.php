<?php

namespace App\Filament\Resources\UserMerchantAccountEntries\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class UserMerchantAccountEntryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('معلومات القيد')
                    ->schema([
                        TextEntry::make('entry_number')
                            ->label('رقم القيد'),

                        TextEntry::make('user.name')
                            ->label('المستخدم'),

                        TextEntry::make('userMerchant.name')
                            ->label('التاجر'),

                        TextEntry::make('entry_type')
                            ->label('نوع القيد')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'debit' => 'danger',
                                'credit' => 'success',
                                'adjustment' => 'warning',
                                default => 'gray',
                            }),

                        TextEntry::make('amount')
                            ->label('المبلغ')
                            ->money('USD'),

                        TextEntry::make('balance_after')
                            ->label('الرصيد بعد المعاملة')
                            ->money('USD'),

                        TextEntry::make('description')
                            ->label('الوصف')
                            ->columnSpanFull(),

                        TextEntry::make('reference_type')
                            ->label('نوع المرجع'),

                        TextEntry::make('reference_id')
                            ->label('معرف المرجع'),

                        TextEntry::make('creator.name')
                            ->label('أنشأ بواسطة'),

                        TextEntry::make('entry_date')
                            ->label('تاريخ القيد')
                            ->date(),

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
