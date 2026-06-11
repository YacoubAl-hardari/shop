<?php

namespace App\Filament\Widgets\Merchant;

use App\Models\PosSale;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class MerchantRecentSalesWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $heading = 'آخر المبيعات';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $teamId = Filament::getTenant()?->id;

        return $table
            ->query(
                PosSale::query()
                    ->where('team_id', $teamId)
                    ->with('merchantCustomer')
                    ->latest()
                    ->limit(10),
            )
            ->columns([
                TextColumn::make('sale_number')
                    ->label('رقم البيع'),
                TextColumn::make('merchantCustomer.name')
                    ->label('العميل')
                    ->placeholder('—'),
                TextColumn::make('total_amount')
                    ->label('الإجمالي')
                ,
                TextColumn::make('payment_type')
                    ->label('نوع الدفع')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d H:i'),
            ])
            ->paginated(false);
    }
}
