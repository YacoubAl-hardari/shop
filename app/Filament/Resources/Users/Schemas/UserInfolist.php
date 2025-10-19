<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('الاسم'),
                TextEntry::make('email')
                    ->label('البريد الإلكتروني'),
                TextEntry::make('role')
                    ->badge()
                    ->label('الدور'),
                TextEntry::make('address')
                    ->placeholder('-')
                    ->label('العنوان'),
                TextEntry::make('phone')
                    ->placeholder('-')
                    ->label('الهاتف'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-')
                    ->label('تاريخ الإنشاء'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-')
                    ->label('تاريخ التحديث'),
            ]);
    }
}
