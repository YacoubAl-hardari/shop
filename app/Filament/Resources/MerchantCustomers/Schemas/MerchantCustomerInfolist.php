<?php

namespace App\Filament\Resources\MerchantCustomers\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MerchantCustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('بيانات العميل')
                ->schema([
                    TextEntry::make('name')->label('الاسم'),
                    TextEntry::make('phone')->label('الهاتف'),
                    TextEntry::make('email')->label('البريد'),
                    TextEntry::make('user.name')->label('المستخدم المرتبط')->placeholder('غير مرتبط'),
                    TextEntry::make('user.email')->label('بريد المستخدم')->placeholder('—'),
                    TextEntry::make('balance')->label('المديونية')->money('SAR'),
                    TextEntry::make('credit_balance')->label('الرصيد الفائض')->money('SAR'),
                    IconEntry::make('is_active')->label('نشط')->boolean(),
                    IconEntry::make('activeStatementShare')
                        ->label('مشاركة كشف الحساب')
                        ->state(fn ($record): bool => $record->isStatementShared())
                        ->boolean()
                        ->trueIcon('heroicon-o-check-circle')
                        ->falseIcon('heroicon-o-x-circle')
                        ->trueColor('success')
                        ->falseColor('gray'),
                ])
                ->columns(3)
                ->columnSpanFull()
                ,
        ]);
    }
}
