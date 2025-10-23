<?php

namespace App\Filament\Resources\MerchantCategories\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\ColorPicker;

class MerchantCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('معلومات تصنيف التاجر')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label('اسم التصنيف')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('icon')
                            ->label('الأيقونة')
                            ->placeholder('heroicon-o-tag')
                            ->maxLength(255),
                    ]),

                    ColorPicker::make('color')
                        ->label('اللون')
                        ->default('#3b82f6'),
                ]),
        ]);
    }
}
