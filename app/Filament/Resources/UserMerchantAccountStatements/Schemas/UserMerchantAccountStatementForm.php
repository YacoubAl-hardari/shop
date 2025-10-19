<?php

namespace App\Filament\Resources\UserMerchantAccountStatements\Schemas;

use App\Models\User;
use App\Models\UserMerchant;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;

class UserMerchantAccountStatementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('user_id')
                    ->label('المستخدم')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('user_merchant_id')
                    ->label('التاجر')
                    ->relationship('userMerchant', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('debit_amount')
                    ->label('مبلغ المدين')
                    ->numeric()
                    ->prefix('$')
                    ->default(0),

                TextInput::make('credit_amount')
                    ->label('مبلغ الدائن')
                    ->numeric()
                    ->prefix('$')
                    ->default(0),

                TextInput::make('balance')
                    ->label('الرصيد')
                    ->numeric()
                    ->prefix('$')
                    ->required(),

                Select::make('transaction_type')
                    ->label('نوع المعاملة')
                    ->options([
                        'order' => 'طلب',
                        'payment' => 'دفع',
                        'refund' => 'استرداد',
                        'adjustment' => 'تعديل',
                    ])
                    ->required(),

                TextInput::make('reference_type')
                    ->label('نوع المرجع')
                    ->maxLength(255),

                TextInput::make('reference_id')
                    ->label('معرف المرجع')
                    ->numeric(),

                Textarea::make('description')
                    ->label('الوصف')
                    ->rows(3)
                    ->columnSpanFull(),

                DatePicker::make('transaction_date')
                    ->label('تاريخ المعاملة')
                    ->required(),
            ]);
    }
}
