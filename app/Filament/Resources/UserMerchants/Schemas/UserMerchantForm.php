<?php

namespace App\Filament\Resources\UserMerchants\Schemas;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Illuminate\Validation\Rule;

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
            ->required()
            ->maxLength(255)
            ->unique('user_merchants', 'email', ignoreRecord: true, modifyRuleUsing: function ($rule) {
                return $rule->where('team_id', Filament::getTenant()->id);
            }),

        TextInput::make('phone')
            ->label('رقم الهاتف')
            ->tel()
            ->required()
            ->maxLength(255)
            ->unique('user_merchants', 'phone', ignoreRecord: true, modifyRuleUsing: function ($rule) {
                return $rule->where('team_id', Filament::getTenant()->id);
            }),

        Textarea::make('information')
            ->label('معلومات إضافية')
            ->rows(3)
            ->columnSpanFull(),

        Toggle::make('is_active')
            ->label('نشط')
            ->default(true),

        Select::make('budget_category_id')
            ->label('فئة الميزانية')
            ->relationship('budgetCategory', 'name', function ($query) {
                return $query->where('user_id',Auth::id())
                    ->where('is_active', true);
            })
            ->searchable()
            ->preload()
            ->placeholder('اختر فئة (اختياري)')
            ->helperText('ربط التاجر بفئة ميزانية لتتبع الإنفاق'),

        Select::make('merchant_category_id')
            ->label('تصنيف التاجر')
            ->relationship('merchantCategory', 'name')
            ->searchable()
            ->preload()
            ->placeholder('اختر تصنيف (اختياري)'),
            
          ])
          ->columnSpanFull()
          ->columns(3),
            ]);
    }
}
