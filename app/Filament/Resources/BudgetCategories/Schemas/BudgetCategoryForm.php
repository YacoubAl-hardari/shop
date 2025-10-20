<?php

namespace App\Filament\Resources\BudgetCategories\Schemas;

use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\ColorPicker;

class BudgetCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('معلومات الفئة')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label('اسم الفئة')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('budget_limit')
                            ->label('حد الميزانية (ريال)')
                            ->required()
                            ->numeric()
                            ->step(0.01),
                    ]),

                    Textarea::make('description')
                        ->label('الوصف')
                        ->rows(3),

                    Grid::make(3)->schema([
                        ColorPicker::make('color')
                            ->label('اللون')
                            ->default('#3b82f6'),

                        TextInput::make('sort_order')
                            ->label('الترتيب')
                            ->numeric()
                            ->default(0),

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

