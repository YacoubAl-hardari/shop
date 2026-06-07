<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('بيانات المورد')
                ->schema([
                    TextInput::make('name')->label('الاسم')->required(),
                    TextInput::make('phone')->label('الهاتف'),
                    TextInput::make('email')->label('البريد')->email(),
                    TextInput::make('tax_number')->label('الرقم الضريبي'),
                    Toggle::make('is_active')->label('نشط')->default(true),
                ])
                ->columns(2)
                ->columnSpanFull()
                ,
        ]);
    }
}
