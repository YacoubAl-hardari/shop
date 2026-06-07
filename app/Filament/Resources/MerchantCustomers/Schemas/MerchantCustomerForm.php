<?php

namespace App\Filament\Resources\MerchantCustomers\Schemas;

use App\Enums\UserType;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MerchantCustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('بيانات العميل')
                ->schema([
                    TextInput::make('name')->label('الاسم')->required()->maxLength(255),
                    TextInput::make('phone')->label('الهاتف')->tel(),
                    TextInput::make('email')->label('البريد الإلكتروني')->email(),
                    Select::make('user_id')
                        ->label('حساب المستخدم المسجّل')
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->options(fn () => User::query()
                            ->where('role', UserType::USER->value)
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn (User $user) => [
                                $user->id => "{$user->name} ({$user->email})",
                            ]))
                        ->helperText('مطلوب لمشاركة كشف الحساب — يجب أن يكون العميل مسجّلاً كمستخدم في النظام'),
                    Toggle::make('is_active')->label('نشط')->default(true),
                ])
                ->columns(3)
                ->columnSpanFull(),
        ]);
    }
}
