<?php

namespace App\Filament\Resources\UserMerchantAccountEntries\Schemas;

use App\Models\User;
use App\Models\UserMerchant;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;

class UserMerchantAccountEntryForm
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

                TextInput::make('entry_number')
                    ->label('رقم القيد')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Select::make('entry_type')
                    ->label('نوع القيد')
                    ->options([
                        'debit' => 'مدين',
                        'credit' => 'دائن',
                        'adjustment' => 'تعديل',
                    ])
                    ->required(),

                TextInput::make('amount')
                    ->label('المبلغ')
                    ->numeric()
                    ->prefix('$')
                    ->required(),

                Textarea::make('description')
                    ->label('الوصف')
                    ->rows(3)
                    ->columnSpanFull(),

                TextInput::make('reference_type')
                    ->label('نوع المرجع')
                    ->maxLength(255),

                TextInput::make('reference_id')
                    ->label('معرف المرجع')
                    ->numeric(),

                TextInput::make('balance_after')
                    ->label('الرصيد بعد المعاملة')
                    ->numeric()
                    ->prefix('$')
                    ->required(),

                DatePicker::make('entry_date')
                    ->label('تاريخ القيد')
                    ->required(),

                Select::make('created_by')
                    ->label('أنشأ بواسطة')
                    ->relationship('creator', 'name')
                    ->searchable()
                    ->preload(),
            ]);
    }
}
