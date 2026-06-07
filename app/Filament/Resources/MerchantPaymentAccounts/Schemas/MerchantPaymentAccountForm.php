<?php

namespace App\Filament\Resources\MerchantPaymentAccounts\Schemas;

use App\Enums\MerchantPaymentAccountType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MerchantPaymentAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('بيانات الحساب')
                ->schema([
                    Select::make('type')
                        ->label('النوع')
                        ->options(MerchantPaymentAccountType::options())
                        ->required()
                        ->native(false),
                    TextInput::make('name')
                        ->label('الاسم')
                        ->placeholder('مثال: الراجحي / مدى / Apple Pay')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('account_number')
                        ->label('رقم الحساب / الآيبان / المحفظة')
                        ->required()
                        ->maxLength(255),
                    Toggle::make('is_active')
                        ->label('نشط')
                        ->default(true),
                    Toggle::make('is_default')
                        ->label('افتراضي لهذا النوع')
                        ->default(false),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ]);
    }
}
