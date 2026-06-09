<?php

namespace App\Filament\Resources\PosSales\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;

class PosSaleInfolist
{
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
                                            ->badge(),
                                        TextEntry::make('payment_method')
                                            ->label('طريقة الدفع')
                                            ->formatStateUsing(fn ($state) => match ($state) {
                                                'cash' => 'نقدي',
                                                'card' => 'شبكة (بطاقة)',
                                                'bank_transfer' => 'تحويل بنكي',
                                                default => $state ?? '—',
                                            }),
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
                                                    ->label('المنتج / الصنف'),
                                                TextEntry::make('quantity')
                                                    ->label('الكمية')
                                                    ->numeric(),
                                                TextEntry::make('unit_price')
                                                    ->label('سعر الوحدة')
                                                    ,
                                                TextEntry::make('total')
                                                    ->label('الإجمالي')
                                                    ,
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
                                    ->label('المبلغ المتبقي')
                                    ,
                            ])
                            ,

                       
                    ])
                    ->columnSpanFull()
                    ,
            ]);
    }
}
