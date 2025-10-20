<?php

namespace App\Filament\Resources\UserMerchantOrders\Tables;

use App\Exports\OrdersExport;
use Filament\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Facades\Excel;

class UserMerchantOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('رقم الطلب')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('userMerchant.name')
                    ->label('التاجر')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total_price')
                    ->label('إجمالي السعر')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('user_merchant_id')
                    ->label('التاجر')
                    ->relationship('userMerchant', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('user_id')
                    ->label('المستخدم')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                // Actions will be handled by the resource
            ])
            ->toolbarActions([
                BulkAction::make('exportExcel')
                    ->label('تصدير إلى Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (Collection $records) {
                        // Load relationships to avoid N+1 queries
                        $records->load(['userMerchant', 'user', 'orderItems.product']);
                        
                        // Generate filename with current date
                        $filename = 'orders_' . now()->format('Y-m-d_His') . '.xlsx';
                        
                        return Excel::download(new OrdersExport($records), $filename);
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
    }
}

