<?php

namespace App\Filament\Resources\StockMovements\Tables;

use App\Enums\StockMovementType;
use App\Models\MerchantProduct;
use App\Exports\StockMovementsExport;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Facades\Excel;

class StockMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('التاريخ والوقت')
                    ->dateTime('Y/m/d H:i')
                    ->sortable()
                    ->timezone('Asia/Riyadh'),

                TextColumn::make('product.name')
                    ->label('المنتج')
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('product', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('barcode', 'like', "%{$search}%")
                                ->orWhere('sku', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),

                BadgeColumn::make('movement_type')
                    ->label('نوع الحركة')
                    ->formatStateUsing(fn(StockMovementType $state) => $state->label())
                    ->color(fn(StockMovementType $state) => $state->color()),

                IconColumn::make('direction')
                    ->label('الاتجاه')
                    ->icon(fn(string $state) => $state === 'in' ? 'heroicon-o-arrow-down-circle' : 'heroicon-o-arrow-up-circle')
                    ->color(fn(string $state) => $state === 'in' ? 'success' : 'danger'),

                TextColumn::make('quantity')
                    ->label('الكمية')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('quantity_before')
                    ->label('الرصيد قبل')
                    ->numeric(decimalPlaces: 2)
                    ->toggleable(),

                TextColumn::make('quantity_after')
                    ->label('الرصيد بعد')
                    ->numeric(decimalPlaces: 2),

                TextColumn::make('unit_cost')
                    ->label('تكلفة الوحدة')

                    ->toggleable(),

                TextColumn::make('total_cost')
                    ->label('إجمالي التكلفة')
                ,

                TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('movement_type')
                    ->label('نوع الحركة')
                    ->options(StockMovementType::options()),

                SelectFilter::make('merchant_product_id')
                    ->label('المنتج')
                    ->options(fn() => MerchantProduct::pluck('name', 'id'))
                    ->searchable(),

                SelectFilter::make('direction')
                    ->label('الاتجاه')
                    ->options(['in' => 'داخل', 'out' => 'خارج']),

                Filter::make('date_range')
                    ->label('الفترة')
                    ->form([
                        DatePicker::make('from')->label('من'),
                        DatePicker::make('to')->label('إلى'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($q, $v) => $q->whereDate('created_at', '>=', $v))
                            ->when($data['to'], fn($q, $v) => $q->whereDate('created_at', '<=', $v));
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->toolbarActions([
                Action::make('exportAll')
                    ->label('تصدير الكل (المفلترة)')
                    ->icon('heroicon-o-funnel')
                    ->color('info')
                    ->action(function ($livewire) {
                        $records = $livewire->getFilteredTableQuery()->with(['product'])->get();
                        $filename = 'stock_movements_filtered_' . now()->format('Y-m-d_His') . '.xlsx';
                        return Excel::download(new StockMovementsExport($records), $filename);
                    }),
                BulkAction::make('exportSelected')
                    ->label('تصدير المحدد إلى Excel')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (Collection $records) {
                        $records->load(['product']);
                        $filename = 'stock_movements_selected_' . now()->format('Y-m-d_His') . '.xlsx';
                        return Excel::download(new StockMovementsExport($records), $filename);
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
    }
}
