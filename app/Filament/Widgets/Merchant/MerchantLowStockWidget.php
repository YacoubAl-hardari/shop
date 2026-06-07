<?php

namespace App\Filament\Widgets\Merchant;

use App\Models\MerchantProduct;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class MerchantLowStockWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $heading = 'تنبيهات المخزون';

    protected static ?string $subheading = 'منتجات بمخزون منخفض (10 وحدات أو أقل)';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $teamId = Filament::getTenant()?->id;

        return $table
            ->query(
                MerchantProduct::query()
                    ->where('team_id', $teamId)
                    ->where('is_active', true)
                    ->where('stock_quantity', '<=', 10)
                    ->orderBy('stock_quantity'),
            )
            ->columns([
                TextColumn::make('name')
                    ->label('المنتج')
                    ->searchable(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->placeholder('—'),
                TextColumn::make('stock_quantity')
                    ->label('المخزون')
                    ->badge()
                    ->color(fn ($state): string => (float) $state <= 0 ? 'danger' : 'warning'),
                TextColumn::make('unit')
                    ->label('الوحدة')
                    ->placeholder('—'),
                TextColumn::make('price')
                    ->label('السعر')
                    ->money('SAR'),
            ])
            ->emptyStateHeading('المخزون جيد')
            ->emptyStateDescription('لا توجد منتجات بمخزون منخفض حالياً')
            ->paginated([5, 10]);
    }
}
