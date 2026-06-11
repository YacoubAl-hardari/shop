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
                TextColumn::make('balance')->label('المديونية'),
                TextColumn::make('credit_balance')->label('الرصيد الفائض'),
                TextColumn::make('user.name')->label('المستخدم المرتبط')->placeholder('—'),
                IconColumn::make('is_statement_shared')
                    ->label('مشاركة الكشف')
                    ->state(fn($record): bool => $record->isStatementShared())
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-minus-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
                IconColumn::make('is_active')->label('نشط')->boolean(),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('statement')
                    ->label('كشف الحساب')
                    ->icon('heroicon-o-document-text')
                    ->url(fn($record) => MerchantCustomerResource::getUrl('statement', ['record' => $record])),
                EditAction::make(),
            ]);
    }
}
