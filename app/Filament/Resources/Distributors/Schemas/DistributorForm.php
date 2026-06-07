<?php

namespace App\Filament\Resources\Distributors\Schemas;

use App\Models\Supplier;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DistributorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('بيانات الموزع')
                ->schema([
                    Select::make('supplier_id')
                        ->label('المورد')
                        ->options(fn () => Supplier::query()->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    TextInput::make('name')->label('الاسم')->required(),
                    TextInput::make('phone')->label('الهاتف'),
                    Textarea::make('contact_info')->label('معلومات التواصل'),
                    Toggle::make('is_active')->label('نشط')->default(true),
                ])
                ->columns(2)
                ->columnSpanFull()
                ,
        ]);
    }
}
