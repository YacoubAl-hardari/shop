<?php

namespace App\Filament\Pages\Tenancy;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Schemas\Schema;

class EditTeamProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'ملف الحساب'; // Account profile in Arabic
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم الحساب') // Account name
                    ->required()
                    ->maxLength(255),
                
                TextInput::make('slug')
                    ->label('المعرف الفريد') // Unique identifier
                    ->required()
                    ->maxLength(255)
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('لا يمكن تغيير المعرف بعد الإنشاء'), // Cannot change identifier after creation
                
                FileUpload::make('avatar_url')
                    ->label('الصورة الرمزية') // Avatar
                    ->image()
                    ->directory('team-avatars')
                    ->visibility('public')
                    ->maxSize(2048),
                
                Textarea::make('description')
                    ->label('الوصف') // Description
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }
}

