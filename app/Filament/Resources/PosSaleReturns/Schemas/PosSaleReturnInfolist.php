<?php

namespace App\Filament\Resources\PosSaleReturns\Schemas;

use App\Enums\RefundMethod;
use App\Enums\ReturnType;
use App\Models\PosSaleReturn;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class PosSaleReturnInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(3)
                    ->schema([
                        // معلومات مستند المرتجع
                        Section::make('معلومات المرتجع / الاستبدال')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('return_number')
                                            ->label('رقم المرتجع'),
                                        TextEntry::make('created_at')
                                            ->label('التاريخ والوقت')
                                            ->dateTime('Y/m/d H:i'),
                                        TextEntry::make('status')
                                            ->label('الحالة')
                                            ->badge()
                                            ->color(fn($state) => $state === 'completed' ? 'success' : 'danger'),
                                        TextEntry::make('originalSale.sale_number')
                                            ->label('رقم الفاتورة الأصلية')
                                            ->url(fn(PosSaleReturn $record) => $record->originalSale
                                                ? route('filament.admin.resources.pos-sales.view', ['record' => $record->pos_sale_id, 'tenant' => $record->team->slug])
                                                : null),
                                        TextEntry::make('return_type')
                                            ->label('نوع العملية')
                                            ->formatStateUsing(fn(ReturnType $state) => $state->label())
                                            ->badge()
                                            ->color(fn(ReturnType $state) => $state === ReturnType::EXCHANGE ? 'warning' : 'danger'),
                                        TextEntry::make('refund_method')
                                            ->label('طريقة الرد / التسوية')
                                            ->formatStateUsing(fn($state) => $state instanceof RefundMethod ? $state->label() : $state)
                                            ->badge()
                                            ->color('gray'),
                                        TextEntry::make('processor.name')
                                            ->label('أنشئ بواسطة')
                                            ->placeholder('—'),
                                    ]),
                            ])
                            ->columnSpanFull(),

                        // الأصناف المرجعة
                        Section::make('الأصناف المرجعة')
                            ->schema([
                                RepeatableEntry::make('returnItems')
                                    ->label(false)
                                    ->schema([
                                        Grid::make(4)
                                            ->schema([
                                                TextEntry::make('product_name')
                                                    ->label('المنتج / الصنف'),
                                                TextEntry::make('quantity_returned')
                                                    ->label('الكمية')
                                                    ->numeric(),
                                                TextEntry::make('unit_price')
                                                    ->label('سعر الوحدة')
                                                ,
                                                TextEntry::make('total_price')
                                                    ->label('الإجمالي')
                                                ,
                                                TextEntry::make('item_condition')
                                                    ->label('حالة الصنف')
                                                    ->formatStateUsing(fn($state) => match ($state) {
                                                        'resellable' => 'قابل لإعادة البيع',
                                                        'damaged' => 'تالف',
                                                        'disposed' => 'للإتلاف',
                                                        default => $state ?? '—'
                                                    }),
                                                TextEntry::make('return_reason')
                                                    ->label('سبب الإرجاع')
                                                    ->formatStateUsing(fn($state) => match ($state) {
                                                        'defective' => 'معيب أو تالف',
                                                        'changed_mind' => 'تغيير رأي',
                                                        'wrong_item' => 'صنف خاطئ',
                                                        'other' => 'أخرى',
                                                        default => $state ?? '—'
                                                    })
                                                    ->placeholder('—'),
                                            ]),
                                    ]),
                            ])
                            ->columnSpanFull(),

                        // الأصناف البديلة (تظهر فقط عند الاستبدال)
                        Section::make('الأصناف البديلة المستلمة')
                            ->visible(fn(PosSaleReturn $record): bool => $record->isExchange())
                            ->schema([
                                RepeatableEntry::make('exchangeItems')
                                    ->label(false)
                                    ->schema([
                                        Grid::make(4)
                                            ->schema([
                                                TextEntry::make('product_name')
                                                    ->label('المنتج / الصنف البديل'),
                                                TextEntry::make('quantity')
                                                    ->label('الكمية')
                                                    ->numeric(),
                                                TextEntry::make('unit_price')
                                                    ->label('سعر الوحدة')
                                                ,
                                                TextEntry::make('total_price')
                                                    ->label('الإجمالي')
                                                ,
                                            ]),
                                    ]),
                            ])
                            ->columnSpanFull(),

                        // ملخص مالي وتوزيع المبالغ
                        Section::make('الملخص المالي للتسوية')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('returned_amount')
                                            ->label('إجمالي قيمة المُرجَع')

                                            ->weight('bold'),
                                        TextEntry::make('exchange_amount')
                                            ->label('إجمالي قيمة البديل')

                                            ->visible(fn(PosSaleReturn $record): bool => $record->isExchange()),
                                        TextEntry::make('price_difference')
                                            ->label('فارق السعر')

                                            ->visible(fn(PosSaleReturn $record): bool => $record->isExchange())
                                            ->color(fn($record) => (float) $record->price_difference > 0
                                                ? 'success'
                                                : ((float) $record->price_difference < 0 ? 'danger' : 'gray')),
                                        TextEntry::make('refunded_to_customer')
                                            ->label('المسترد نقداً للعميل')

                                            ->visible(fn(PosSaleReturn $record): bool => (float) $record->refunded_to_customer > 0),
                                        TextEntry::make('receivable_reduction_amount')
                                            ->label('الخصم من كشف الحساب')

                                            ->visible(fn(PosSaleReturn $record): bool => (float) $record->receivable_reduction_amount > 0),
                                        TextEntry::make('charged_to_customer')
                                            ->label('الفرق المحصل من العميل')

                                            ->visible(fn(PosSaleReturn $record): bool => (float) $record->charged_to_customer > 0),
                                        TextEntry::make('credit_note_amount')
                                            ->label('المضاف للرصيد الدائن للعميل')

                                            ->visible(fn(PosSaleReturn $record): bool => (float) $record->credit_note_amount > 0),
                                    ]),
                            ])
                            ->columnSpanFull(),

                        // ملاحظات
                        Section::make('ملاحظات المرتجع')
                            ->schema([
                                TextEntry::make('notes')
                                    ->label(false)
                                    ->placeholder('لا توجد ملاحظات'),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
