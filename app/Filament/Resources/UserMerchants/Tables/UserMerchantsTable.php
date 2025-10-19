<?php

namespace App\Filament\Resources\UserMerchants\Tables;

use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use App\Filament\Resources\UserMerchants\UserMerchantResource;

class UserMerchantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('اسم التاجر')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('رقم الهاتف')
                    ->searchable(),

                TextColumn::make('balance')
                    ->label('الرصيد')
                    ->money('USD')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean(),

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
                TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->placeholder('جميع التجار')
                    ->trueLabel('التجار النشطين فقط')
                    ->falseLabel('التجار غير النشطين فقط'),
            ])
            ->recordActions([
                Action::make('financial_stats')
                    ->label('الإحصائيات المالية')
                    ->icon('heroicon-o-chart-bar')
                    ->color('success')
                    ->url(fn ($record) => UserMerchantResource::getUrl('financial-stats', ['record' => $record])),
            ])
            ->bulkActions([
                // Bulk actions will be handled by the resource
            ]);
    }
}
