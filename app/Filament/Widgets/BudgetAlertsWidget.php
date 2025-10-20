<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use App\Models\BudgetAlert;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\TableWidget as BaseWidget;

class BudgetAlertsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    public function getHeading(): ?string
    {
        return 'تنبيهات الميزانية';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                BudgetAlert::where('user_id', Auth::id())
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\IconColumn::make('type')
                    ->label('')
                    ->icon(fn ($record) => $record->type->getIcon())
                    ->color(fn ($record) => $record->type->getColor()),

                Tables\Columns\TextColumn::make('title')
                    ->label('التنبيه')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->message),

                Tables\Columns\TextColumn::make('trigger_percentage')
                    ->label('النسبة')
                    ->formatStateUsing(fn ($state) => $state ? $state . '%' : '-')
                    ->badge()
                    ->color(fn ($record) => $record->type->getColor()),

                Tables\Columns\TextColumn::make('current_amount')
                    ->label('المبلغ')
                    ->money('SAR'),

                Tables\Columns\IconColumn::make('is_read')
                    ->label('مقروء')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-envelope')
                    ->trueColor('gray')
                    ->falseColor('warning'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d H:i')
                    ->since(),
            ])
            ->recordActions([
                Action::make('mark_as_read')
                    ->label('تمييز كمقروء')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => !$record->is_read)
                    ->action(fn ($record) => $record->markAsRead()),
            ])
            ->paginated([5, 10])
            ->emptyStateHeading('لا توجد تنبيهات')
            ->emptyStateDescription('ستظهر هنا التنبيهات عند اقتراب أو تجاوز الميزانية')
            ->emptyStateIcon('heroicon-o-bell');
    }
}

