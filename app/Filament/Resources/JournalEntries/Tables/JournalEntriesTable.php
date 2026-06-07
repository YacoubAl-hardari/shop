<?php

namespace App\Filament\Resources\JournalEntries\Tables;

use App\Exports\JournalEntriesExport;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class JournalEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('entry_number')
                    ->label('رقم القيد')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('entry_date')
                    ->label('التاريخ')
                    ->date()
                    ->sortable(),

                TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->arabicLabel()),

                TextColumn::make('creator.name')
                    ->label('أنشئ بواسطة'),
            ])
            ->filters([
                DateRangeFilter::make('entry_date')
                    ->label('تاريخ القيد'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkAction::make('exportExcel')
                    ->label('تصدير إلى Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (Collection $records) {
                        $filename = 'journal_entries_'.now()->format('Y-m-d_His').'.xlsx';

                        return Excel::download(new JournalEntriesExport($records), $filename);
                    })
                    ->deselectRecordsAfterCompletion(),
            ])
            ->defaultSort('entry_date', 'desc');
    }
}
