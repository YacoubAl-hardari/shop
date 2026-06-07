<?php

namespace App\Filament\Resources\MerchantCustomers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MerchantCustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('بيانات العميل')
                ->schema([
                    TextInput::make('name')->label('الاسم')->required()->maxLength(255),
                    TextInput::make('phone')->label('الهاتف')->tel(),
                    TextInput::make('email')->label('البريد الإلكتروني')->email(),
                    Toggle::make('is_active')->label('نشط')->default(true),
                ])
                ->columns(3)
                ->columnSpanFull()
                ,
        ]);
    }
}
