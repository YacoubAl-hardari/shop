<?php

namespace App\Filament\Resources\UserMerchants\Schemas;

use App\Models\User;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class UserMerchantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
          Section::make('معلومات التاجر')
          ->schema([

        TextInput::make('name')
            ->label('اسم التاجر')
            ->required()
            ->maxLength(255),

        TextInput::make('email')
            ->label('البريد الإلكتروني')
            ->email()
            ->maxLength(255),

        TextInput::make('phone')
            ->label('رقم الهاتف')
            ->tel()
            ->maxLength(255),

        Textarea::make('information')
            ->label('معلومات إضافية')
            ->rows(3)
            ->columnSpanFull(),

        Toggle::make('is_active')
            ->label('نشط')
            ->default(true),
            
          ])
          ->columnSpanFull()
          ->columns(3),
            ]);
    }
}
