<?php

namespace App\Filament\Resources\MerchantProducts\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class MerchantProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('الاسم')->searchable(),
                TextColumn::make('barcode')->label('الباركود')->searchable()->toggleable(),
                TextColumn::make('sku')->label('الرمز')->toggleable(),
                TextColumn::make('supplier.name')->label('المورد')->placeholder('—')->toggleable(),
                TextColumn::make('distributor.name')->label('الموزع')->placeholder('—')->toggleable(),
                TextColumn::make('price')->label('السعر')->money('SAR'),
                TextColumn::make('stock_quantity')->label('المخزون'),
                IconColumn::make('is_active')->label('نشط')->boolean(),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('printBarcode')
                    ->label('طباعة الباركود')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->modalWidth('md')
                    ->modalHeading('خيارات طباعة ملصق الباركود')
                    ->form([
                        Select::make('size')
                            ->label('مقاس الملصق')
                            ->options([
                                '50x30' => '50 × 30 مم (موصى به)',
                                '58x40' => '58 × 40 مم',
                            ])
                            ->default('50x30')
                            ->required(),
                        TextInput::make('qty')
                            ->label('عدد النسخ للطباعة')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->default(1)
                            ->required(),
                    ])
                    ->action(function ($record, array $data, $livewire) {
                        $url = route('merchant.products.print-barcodes', [
                            'tenant' => \Filament\Facades\Filament::getTenant()->slug,
                            'ids' => [$record->id],
                            'size' => $data['size'],
                            'qty' => $data['qty'],
                        ]);
                        $livewire->js("window.open('{$url}', '_blank')");
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                    BulkAction::make('printBarcodesBulk')
                        ->label('طباعة الباركود للمحدّد')
                        ->icon('heroicon-o-printer')
                        ->color('success')
                        ->modalWidth('md')
                        ->modalHeading('خيارات طباعة ملصقات الباركود للمحدّد')
                        ->form([
                            Select::make('size')
                                ->label('مقاس الملصق')
                                ->options([
                                    '50x30' => '50 × 30 مم (موصى به)',
                                    '58x40' => '58 × 40 مم',
                                ])
                                ->default('50x30')
                                ->required(),
                            TextInput::make('qty')
                                ->label('عدد النسخ للطباعة (لكل منتج)')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(100)
                                ->default(1)
                                ->required(),
                        ])
                        ->action(function (\Illuminate\Support\Collection $records, array $data, $livewire) {
                            $ids = $records->pluck('id')->toArray();
                            $url = route('merchant.products.print-barcodes', [
                                'tenant' => \Filament\Facades\Filament::getTenant()->slug,
                                'ids' => $ids,
                                'size' => $data['size'],
                                'qty' => $data['qty'],
                            ]);
                            $livewire->js("window.open('{$url}', '_blank')");
                        }),
                ]),
            ]);
    }
}
