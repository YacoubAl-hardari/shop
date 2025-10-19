<?php

namespace App\Filament\Resources\UserMerchantPaymentTransactions\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class UserMerchantPaymentTransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('معلومات معاملة الدفع')
                    ->schema([
                        TextEntry::make('transaction_number')
                            ->label('رقم المعاملة'),

                        TextEntry::make('user.name')
                            ->label('المستخدم'),

                        TextEntry::make('userMerchant.name')
                            ->label('التاجر'),

                        TextEntry::make('userMerchantWallet.account_name')
                            ->label('محفظة التاجر'),

                        TextEntry::make('amount')
                            ->label('المبلغ')
                            ->money('USD'),

                        TextEntry::make('payment_method')
                            ->label('طريقة الدفع')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'bank_transfer' => 'primary',
                                'cash' => 'success',
                                'check' => 'warning',
                                'credit_card' => 'info',
                                'digital_wallet' => 'secondary',
                                default => 'gray',
                            }),

                        TextEntry::make('status')
                            ->label('الحالة')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'completed' => 'success',
                                'failed' => 'danger',
                                'cancelled' => 'gray',
                                default => 'gray',
                            }),

                        TextEntry::make('reference_number')
                            ->label('رقم المرجع'),

                        TextEntry::make('notes')
                            ->label('ملاحظات')
                            ->columnSpanFull(),

                        TextEntry::make('payment_date')
                            ->label('تاريخ الدفع')
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
