<?php

namespace App\Filament\Resources\UserMerchantWallets\Schemas;

use App\Models\UserMerchant;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class UserMerchantWalletForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('معلومات المحفظة')
                ->schema([
                    Select::make('user_merchant_id')
                    ->label('التاجر')
                    ->relationship('userMerchant', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('account_name')
                    ->label('اسم الحساب')
                    ->required()
                    ->maxLength(255),

                TextInput::make('bank_account_number')
                    ->label('رقم الحساب المصرفي')
                    ->required()
                    ->maxLength(255),

                TextInput::make('bank_name')
                    ->label('اسم البنك')
                    ->required()
                    ->maxLength(255),

                Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true),
                ])
                ->columns(4)
                ->columnSpanFull()
            ]);
    }
}
