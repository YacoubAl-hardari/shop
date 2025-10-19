<?php

namespace App\Filament\Resources\UserMerchantPaymentTransactions\Schemas;

use App\Enums\PaymentMethod;
use App\Models\User;
use App\Models\UserMerchant;
use Filament\Schemas\Schema;
use App\Models\UserMerchantWallet;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;

class UserMerchantPaymentTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
               Section::make('معلومات الدفع')
               ->schema([

            
                Select::make('user_merchant_id')
                ->label('التاجر')
                ->relationship('userMerchant', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->live()
                ->afterStateUpdated(function (callable $set) {
                    // Clear wallet selection when merchant changes
                    $set('user_merchant_wallet_id', null);
                }),

            Select::make('user_merchant_wallet_id')
                ->label('محفظة التاجر')
                ->options(function (callable $get) {
                    $merchantId = $get('user_merchant_id');
                    if (!$merchantId) {
                        return [];
                    }
                    
                    return UserMerchantWallet::where('user_merchant_id', $merchantId)
                        ->pluck('account_name', 'id');
                })
                ->searchable()
                ->required()
                ->visible(fn (callable $get) => !empty($get('user_merchant_id'))),

            TextInput::make('transaction_number')
                ->label('رقم المعاملة')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),

            TextInput::make('amount')
                ->label('المبلغ')
                ->numeric()
                ->prefix('$')
                ->required(),

            Select::make('payment_method')
                ->label('طريقة الدفع')
                ->options(PaymentMethod::toArray())
                ->required(),


                
                TextInput::make('reference_number')
                ->label('رقم المرجع')
                ->maxLength(255),
                
                DatePicker::make('payment_date')
                ->label('تاريخ الدفع')
                ->required(),

                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->rows(3)
                    ->columnSpanFull(),


               ])
               ->columnSpanFull()
               ->columns(7)
            ]);
    }
}
