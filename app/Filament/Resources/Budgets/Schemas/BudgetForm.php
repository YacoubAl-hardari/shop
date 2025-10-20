<?php

namespace App\Filament\Resources\Budgets\Schemas;

use App\Enums\BudgetPeriod;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;

class BudgetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('معلومات الميزانية')
                ->schema([
                    TextInput::make('name')
                        ->label('اسم الميزانية')
                        ->required()
                        ->maxLength(255),

                    Textarea::make('description')
                        ->label('الوصف')
                        ->rows(3),

                    Grid::make(2)->schema([
                        Select::make('period')
                            ->label('الفترة')
                            ->required()
                            ->options(BudgetPeriod::toArray())
                            ->default('monthly'),

                        TextInput::make('total_limit')
                            ->label('حد الميزانية (ريال)')
                            ->required()
                            ->numeric()
                            ->step(0.01),
                    ]),

                    Grid::make(2)->schema([
                        DatePicker::make('start_date')
                            ->label('تاريخ البداية')
                            ->required()
                            ->default(now()),

                        DatePicker::make('end_date')
                            ->label('تاريخ النهاية')
                            ->required(),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('alert_percentage')
                            ->label('نسبة التنبيه (%)')
                            ->numeric()
                            ->default(80),

                        Toggle::make('is_active')
                            ->label('نشطة')
                            ->default(true),
                    ]),

                    Hidden::make('user_id')
                        ->default(Auth::id()),
                ]),
        ]);
    }
}

