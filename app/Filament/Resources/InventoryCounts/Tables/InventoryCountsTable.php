<?php

namespace App\Filament\Resources\InventoryCounts\Tables;

use App\Enums\InventoryCountStatus;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Actions\Action;

class InventoryCountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('count_number')
                    ->label('رقم الجرد')
                    ->searchable(),

                TextColumn::make('fiscal_year')
                    ->label('السنة المالية')
                    ->sortable(),

                TextColumn::make('count_date')
                    ->label('تاريخ الجرد')
                    ->date('Y/m/d')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn(InventoryCountStatus $state) => $state->label())
                    ->color(fn(InventoryCountStatus $state) => $state->color()),

                TextColumn::make('total_book_value')
                    ->label('القيمة الدفترية')
                ,

                TextColumn::make('total_counted_value')
                    ->label('القيمة الفعلية')
                ,

                TextColumn::make('variance_value')
                    ->label('الفارق')

                    ->color(fn($record) => (float) $record->variance_value > 0 ? 'success'
                        : ((float) $record->variance_value < 0 ? 'danger' : 'gray')),

                TextColumn::make('creator.name')
                    ->label('أنشأه')
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Action::make('open')
                    ->label('فتح')
                    ->icon(Heroicon::OutlinedEye)
                    ->url(fn($record) => \App\Filament\Resources\InventoryCounts\InventoryCountResource::getUrl('view', ['record' => $record])),
            ])
            ->headerActions([]);
    }
}
