<?php

namespace App\Filament\Resources\UserMerchantProducts\Schemas;

use App\Models\UserMerchant;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\FileUpload;
use JeffersonGoncalves\Filament\QrCodeField\Forms\Components\QrCodeInput;

class UserMerchantProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
             Section::make('معلومات المنتج')
             ->schema([
                Select::make('user_merchant_id')
                ->label('التاجر')
                ->relationship('userMerchant', 'name')
                ->searchable()
                ->preload()
                ->required(),

            TextInput::make('name')
                ->label('اسم المنتج')
                ->required()
                ->maxLength(255),

            TextInput::make('price')
                ->label('السعر')
                ->numeric()
                ->prefix('$')
                ->required(),

                QrCodeInput::make('barcode')
                ->label('الباركود')
                ->maxLength(255)
                ->required()
                ->icon('heroicon-o-qr-code'),

          Section::make('معلومات المنتج')
          ->label('معلومات المنتج')
          ->columnSpanFull()
          ->collapsed()
          ->schema([
              
                      TextInput::make('brand')
                          ->label('العلامة التجارية')
                          ->columnSpanFull()
                          ->maxLength(255),
            Textarea::make('description')
            ->label('الوصف')
            ->rows(3)
            ->columnSpanFull(),

        FileUpload::make('image')
            ->label('صورة المنتج')
            ->image()
            ->columnSpanFull()
            ->directory('products')
            ->visibility('public'),
          ]),

            Toggle::make('is_active')
                ->label('نشط')
                ->default(true),
             ])
             ->columnSpanFull()
             ->columns(4)
            ]);
    }
}
