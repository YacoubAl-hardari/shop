<?php

namespace App\Filament\Resources\MerchantCustomers\Tables;

use App\Filament\Resources\MerchantCustomers\MerchantCustomerResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MerchantCustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('الاسم')->searchable(),
                TextColumn::make('phone')->label('الهاتف'),
                TextColumn::make('email')->label('البريد'),
                TextColumn::make('balance')->label('المديونية')->money('SAR'),
                TextColumn::make('credit_balance')->label('الرصيد الفائض')->money('SAR'),
                IconColumn::make('is_active')->label('نشط')->boolean(),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('statement')
                    ->label('كشف الحساب')
                    ->icon('heroicon-o-document-text')
                    ->url(fn ($record) => MerchantCustomerResource::getUrl('statement', ['record' => $record])),
                EditAction::make(),
            ]);
    }
}
