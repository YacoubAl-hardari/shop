<?php

namespace App\Filament\Resources\UserMerchantAccountStatements\Tables;

use App\Exports\AccountStatementsExport;
use Filament\Actions\BulkAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class UserMerchantAccountStatementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('userMerchant.name')
                    ->label('التاجر')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('debit_amount')
                    ->label('مبلغ المدين')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('credit_amount')
                    ->label('مبلغ الدائن')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('balance')
                    ->label('الرصيد')
                    ->money('USD')
                    ->sortable(),

                BadgeColumn::make('transaction_type')
                    ->label('نوع المعاملة')
                    ->colors([
                        'primary' => 'order',
                        'success' => 'payment',
                        'warning' => 'refund',
                        'info' => 'adjustment',
                    ]),

                TextColumn::make('transaction_date')
                    ->label('تاريخ المعاملة')
                    ->date()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('المستخدم')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('user_merchant_id')
                    ->label('التاجر')
                    ->relationship('userMerchant', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('transaction_type')
                    ->label('نوع المعاملة')
                    ->options([
                        'order' => 'طلب',
                        'payment' => 'دفع',
                        'refund' => 'استرداد',
                        'adjustment' => 'تعديل',
                    ]),

                    DateRangeFilter::make('transaction_date')
                    ->label('تاريخ المعاملة'),
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
                        $records->load(['userMerchant', 'user']);
                        
                        // Generate filename with current date
                        $filename = 'account_statements_' . now()->format('Y-m-d_His') . '.xlsx';
                        
                        return Excel::download(new AccountStatementsExport($records), $filename);
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
    }
}

