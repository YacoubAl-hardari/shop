<?php

namespace App\Filament\Resources\UserMerchantAccountEntries\Tables;

use App\Exports\AccountEntriesExport;
use Filament\Actions\BulkAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class UserMerchantAccountEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('entry_number')
                    ->label('رقم القيد')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('userMerchant.name')
                    ->label('التاجر')
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('entry_type')
                    ->label('نوع القيد')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'debit' => 'مدين',
                        'credit' => 'دائن',
                        'adjustment' => 'تعديل',
                        default => $state,
                    })
                    ->colors([
                        'danger' => 'debit',
                        'success' => 'credit',
                        'warning' => 'adjustment',
                    ]),

                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('balance_after')
                    ->label('الرصيد بعد المعاملة')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('entry_date')
                    ->label('تاريخ القيد')
                    ->date()
                    ->sortable(),

                TextColumn::make('creator.name')
                    ->label('أنشأ بواسطة')
                    ->searchable()
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

                SelectFilter::make('entry_type')
                    ->label('نوع القيد')
                    ->options([
                        'debit' => 'مدين',
                        'credit' => 'دائن',
                        'adjustment' => 'تعديل',
                    ]),

                    DateRangeFilter::make('entry_date')
                    ->label('تاريخ القيد'),
            ])
            ->actions([
                // Actions will be handled by the resource
            ])
            ->bulkActions([
                BulkAction::make('exportExcel')
                    ->label('تصدير إلى Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (Collection $records) {
                        // Load relationships to avoid N+1 queries
                        $records->load(['userMerchant', 'user', 'creator']);
                        
                        // Generate filename with current date
                        $filename = 'account_entries_' . now()->format('Y-m-d_His') . '.xlsx';
                        
                        return Excel::download(new AccountEntriesExport($records), $filename);
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
    }
}

