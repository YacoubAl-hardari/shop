<?php

namespace App\Filament\Resources\MerchantCategories\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Components\ColorEntry;
use Filament\Schemas\Components\Grid;

class MerchantCategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('معلومات تصنيف التاجر')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('name')
                            ->label('اسم التصنيف'),

                        ColorEntry::make('color')
                            ->label('اللون'),
                    ]),

                    TextEntry::make('icon')
                        ->label('الأيقونة'),
                ]),
        ]);
    }
}
