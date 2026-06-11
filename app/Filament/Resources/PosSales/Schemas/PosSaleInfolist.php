<?php

namespace App\Filament\Resources\PosSales\Schemas;

use App\Enums\SalePaymentType;
use App\Models\PosSale;
use App\Models\PosSaleItem;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;

class PosSaleInfolist
{
    private static function returnedItemAttributes(PosSaleItem $item): array
    {
        return $item->hasBeenReturned()
            ? ['class' => 'line-through text-danger-600 dark:text-danger-400']
            : [];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(3)
                    ->schema([
                        Section::make('معلومات الفاتورة')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('sale_number')
                                            ->label('رقم الفاتورة'),
                                        TextEntry::make('created_at')
                                            ->label('التاريخ والوقت')
                                            ->dateTime('Y/m/d H:i'),
                                        TextEntry::make('status')
                                            ->label('الحالة')
                                            ->badge()
                                            ->color(fn ($state) => $state === 'completed' ? 'success' : 'danger'),
                                        TextEntry::make('merchantCustomer.name')
                                            ->label('العميل')
                                            ->default('عميل نقدي'),
                                        TextEntry::make('payment_type')
                                            ->label('نوع الدفع')
                                            ->formatStateUsing(fn (SalePaymentType $state): string => $state->displayLabel())
                                            ->badge()
                                            ->color(fn (SalePaymentType $state): string => match ($state) {
                                                SalePaymentType::CASH => 'success',
                                                SalePaymentType::CREDIT => 'danger',
                                                SalePaymentType::PARTIAL => 'warning',
                                            }),
                                        TextEntry::make('payment_method')
                                            ->label('طريقة السداد')
                                            ->formatStateUsing(fn ($state, PosSale $record): string => $record->paymentMethodLabel() ?? '—')
                                            ->placeholder('—'),
                                        TextEntry::make('paymentAccount.name')
                                            ->label('حساب السداد')
                                            ->visible(fn (PosSale $record): bool => filled($record->merchant_payment_account_id)),
                                        TextEntry::make('payment_reference')
                                            ->label('مرجع العملية')
                                            ->visible(fn (PosSale $record): bool => filled($record->payment_reference)),
                                    ]),
                            ])
                              ->columnSpanFull()
                            
                            ,

                             Section::make('الأصناف المباعة')
                            ->schema([
                                RepeatableEntry::make('items')
                                    ->label(false)
                                    ->schema([
                                        Grid::make(4)
                                            ->schema([
                                                TextEntry::make('product_name')
                                                    ->label('المنتج / الصنف')
                                                    ->extraAttributes(fn (PosSaleItem $record): array => self::returnedItemAttributes($record)),
                                                TextEntry::make('quantity')
                                                    ->label('الكمية')
                                                    ->numeric()
                                                    ->extraAttributes(fn (PosSaleItem $record): array => self::returnedItemAttributes($record)),
                                                TextEntry::make('unit_price')
                                                    ->label('سعر الوحدة')
                                                    ->extraAttributes(fn (PosSaleItem $record): array => self::returnedItemAttributes($record)),
                                                TextEntry::make('total')
                                                    ->label('الإجمالي')
                                                    ->extraAttributes(fn (PosSaleItem $record): array => self::returnedItemAttributes($record)),
                                            ]),
                                    ]),
                            ])
                              ->columnSpanFull()
                            ,

                        Section::make('ملاحظات')
                            ->schema([
                                TextEntry::make('notes')
                                    ->label(false)
                                    ->placeholder('لا توجد ملاحظات'),
                            ]),

                        Section::make('الملخص المالي')
                          
                            ->schema([
                                TextEntry::make('total_amount')
                                    ->label('إجمالي الفاتورة')
                                    
                                    ->weight('bold'),
                                TextEntry::make('customer_credit_applied')
                                    ->label('رصيد مستخدم')
                                    ,
                                TextEntry::make('paid_amount')
                                    ->label('المبلغ المدفوع')
                                    ,
                                TextEntry::make('credit_amount')
                                    ->label(fn (PosSale $record): string => $record->payment_type === SalePaymentType::CREDIT
                                        || (float) $record->credit_amount > 0
                                        ? 'المتبقي في الذمة'
                                        : 'المبلغ المتبقي')
                                    ,
                            ])
                            ,

                       
                    ])
                    ->columnSpanFull()
                    ,
            ]);
    }
}
