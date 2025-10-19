<?php

namespace App\Filament\Resources\UserMerchantAccountStatements\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class UserMerchantAccountStatementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('معلومات كشف الحساب')
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('المستخدم'),

                        TextEntry::make('userMerchant.name')
                            ->label('التاجر'),

                        TextEntry::make('debit_amount')
                            ->label('مبلغ المدين')
                            ->money('USD'),

                        TextEntry::make('credit_amount')
                            ->label('مبلغ الدائن')
                            ->money('USD'),

                        TextEntry::make('balance')
                            ->label('الرصيد')
                            ->money('USD'),

                        TextEntry::make('transaction_type')
                            ->label('نوع المعاملة')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'order' => 'primary',
                                'payment' => 'success',
                                'refund' => 'warning',
                                'adjustment' => 'info',
                                default => 'gray',
                            }),

                        TextEntry::make('reference_type')
                            ->label('نوع المرجع'),

                        TextEntry::make('reference_id')
                            ->label('معرف المرجع'),

                        TextEntry::make('description')
                            ->label('الوصف')
                            ->columnSpanFull(),

                        TextEntry::make('transaction_date')
                            ->label('تاريخ المعاملة')
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
