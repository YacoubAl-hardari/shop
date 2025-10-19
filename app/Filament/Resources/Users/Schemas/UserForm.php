<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DateTimePicker;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
               Section::make('معلومات المستخدم')
                ->schema([
                    TextInput::make('name')
                    ->label('الاسم')
                    ->required(),
                TextInput::make('email')
                    ->label('البريد الإلكتروني')
                    ->email()
                    ->required(),
                TextInput::make('password')
                    ->label('كلمة المرور')
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null),
                Select::make('role')
                    ->label('الدور')
                    ->options(['admin' => 'مدير', 'user' => 'مستخدم'])
                    ->default('user')
                    ->required(),
                TextInput::make('address')
                    ->label('العنوان')
                    ->required(),
                TextInput::make('phone')
                    ->label('الهاتف')
                    ->tel()
                    ->required(),
                ])
                ->columnSpanFull()
                ->columns(3),

                Section::make('الإعدادات المالية')
                    ->description('حدد راتبك وحدود المشتريات والديون للمراقبة المالية والحصول على تنبيهات')
                    ->schema([
                        TextInput::make('salary')
                            ->label('الراتب الشهري')
                            ->numeric()
                            ->prefix('ريال')
                            ->minValue(0)
                            ->step(0.01)
                            ->helperText('أدخل راتبك الشهري لحساب نسبة المخاطر المالية'),
                        
                        TextInput::make('min_spending_limit')
                            ->label('الحد الأدنى للمشتريات')
                            ->numeric()
                            ->prefix('ريال')
                            ->minValue(0)
                            ->step(0.01)
                            ->helperText('سيتم إشعارك إذا كانت قيمة الطلب أقل من هذا الحد'),
                        
                        TextInput::make('max_spending_limit')
                            ->label('الحد الأقصى للمشتريات')
                            ->numeric()
                            ->prefix('ريال')
                            ->minValue(0)
                            ->step(0.01)
                            ->helperText('سيتم تنبيهك إذا تجاوزت قيمة الطلب هذا الحد'),
                        
                        TextInput::make('max_debt_limit')
                            ->label('الحد الأقصى للديون')
                            ->numeric()
                            ->prefix('ريال')
                            ->minValue(0)
                            ->step(0.01)
                            ->helperText('سيتم تنبيهك إذا تجاوزت ديونك هذا الحد'),
                        
                        TextInput::make('debt_warning_percentage')
                            ->label('نسبة التحذير من الراتب')
                            ->numeric()
                            ->suffix('%')
                            ->default(50)
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(1)
                            ->helperText('سيتم إرسال تحذير عندما تصل الديون لهذه النسبة من راتبك'),
                        
                        TextInput::make('debt_danger_percentage')
                            ->label('نسبة الخطر من الراتب')
                            ->numeric()
                            ->suffix('%')
                            ->default(80)
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(1)
                            ->helperText('سيتم إرسال تنبيه خطر عندما تصل الديون لهذه النسبة من راتبك'),
                    ])
                    ->columnSpanFull()
                    ->columns(3)
                    ->collapsible()
            ]);
    }
}
