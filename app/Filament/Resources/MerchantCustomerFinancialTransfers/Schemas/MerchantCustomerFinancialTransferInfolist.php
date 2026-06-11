<?php

namespace App\Filament\Resources\MerchantCustomerFinancialTransfers\Schemas;

use App\Enums\CustomerFinancialTransferPurpose;
use App\Enums\CustomerFinancialTransferStatus;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MerchantCustomerFinancialTransferInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('ملخص الطلب')
                ->description('نظرة سريعة على حالة التحويل والمبلغ')
                ->schema([
                    Grid::make(4)
                        ->schema([
                            TextEntry::make('status')
                                ->label('الحالة')
                                ->badge()
                                ->color(fn($state): string => $state instanceof CustomerFinancialTransferStatus
                                    ? $state->getColor()
                                    : 'gray')
                                ->formatStateUsing(fn($state): string => $state instanceof CustomerFinancialTransferStatus
                                    ? $state->getLabel()
                                    : (string) $state),
                            TextEntry::make('purpose')
                                ->label('الغرض')
                                ->badge()
                                ->color(fn($state): string => $state instanceof CustomerFinancialTransferPurpose
                                    ? ($state === CustomerFinancialTransferPurpose::PREPAID ? 'info' : 'success')
                                    : 'gray')
                                ->formatStateUsing(fn($state): string => $state instanceof CustomerFinancialTransferPurpose
                                    ? $state->getLabel()
                                    : (string) $state),
                            TextEntry::make('amount')
                                ->label('المبلغ')

                                ->weight('bold'),
                            TextEntry::make('created_at')
                                ->label('تاريخ الطلب')
                                ->dateTime('Y/m/d H:i'),
                        ]),
                ])
                ->columnSpanFull(),

            Grid::make(2)
                ->schema([
                    Section::make('بيانات العميل')
                        ->schema([
                            TextEntry::make('merchantCustomer.name')
                                ->label('اسم العميل'),
                            TextEntry::make('merchantCustomer.phone')
                                ->label('الهاتف')
                                ->placeholder('—'),
                            TextEntry::make('submitter.name')
                                ->label('مقدّم الطلب'),
                        ])
                        ->columns(1),

                    Section::make('تفاصيل الدفع')
                        ->schema([
                            TextEntry::make('payment_method')
                                ->label('طريقة الدفع')
                                ->badge()
                                ->color(fn(?string $state): string => match ($state) {
                                    'cash' => 'success',
                                    'card' => 'info',
                                    'bank_transfer' => 'primary',
                                    default => 'gray',
                                })
                                ->formatStateUsing(fn(?string $state): string => self::paymentMethodLabel($state)),
                            TextEntry::make('paymentAccount.name')
                                ->label('حساب الاستلام')
                                ->placeholder('—')
                                ->visible(fn($record): bool => in_array($record->payment_method, ['card', 'bank_transfer'], true)),
                            TextEntry::make('paymentAccount.account_number')
                                ->label('رقم الحساب / البطاقة')
                                ->copyable()
                                ->placeholder('—')
                                ->visible(fn($record): bool => $record->paymentAccount !== null),
                            TextEntry::make('reference_number')
                                ->label('مرجع العملية')
                                ->copyable()
                                ->placeholder('—'),
                        ])
                        ->columns(2),
                ])
                ->columnSpanFull(),

            Section::make('ملاحظات العميل')
                ->schema([
                    TextEntry::make('notes')
                        ->label(false)
                        ->placeholder('لا توجد ملاحظات'),
                ])
                ->visible(fn($record): bool => filled($record->notes))
                ->columnSpanFull(),

            Section::make('المراجعة')
                ->description('تفاصيل تأكيد أو رفض التاجر')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextEntry::make('reviewer.name')
                                ->label('راجعها')
                                ->placeholder('—'),
                            TextEntry::make('reviewed_at')
                                ->label('تاريخ المراجعة')
                                ->dateTime('Y/m/d H:i')
                                ->placeholder('—'),
                            TextEntry::make('merchantCustomerPayment.id')
                                ->label('رقم السداد المسجّل')
                                ->placeholder('—')
                                ->visible(fn($record): bool => $record->merchant_customer_payment_id !== null),
                        ]),
                    TextEntry::make('rejection_reason')
                        ->label('سبب الرفض')
                        ->color('danger')
                        ->columnSpanFull()
                        ->visible(fn($record): bool => $record->status === CustomerFinancialTransferStatus::REJECTED),
                ])
                ->visible(fn($record): bool => $record->reviewed_by !== null
                    || $record->status === CustomerFinancialTransferStatus::REJECTED
                    || $record->status === CustomerFinancialTransferStatus::APPROVED)
                ->columnSpanFull(),
        ]);
    }

    private static function paymentMethodLabel(?string $method): string
    {
        return match ($method) {
            'cash' => 'نقد',
            'card' => 'بطاقة',
            'bank_transfer' => 'تحويل بنكي',
            default => $method ?? '—',
        };
    }
}
