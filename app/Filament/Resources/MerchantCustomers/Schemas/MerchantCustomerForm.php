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
                    TextInput::make('name')
                        ->label('الاسم')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('phone')
                        ->label('الهاتف')
                        ->tel()
                        ->live(onBlur: true),
                    TextInput::make('email')
                        ->label('البريد الإلكتروني')
                        ->email()
                        ->live(onBlur: true),
                    Select::make('user_id')
                        ->label('حساب المستخدم المسجّل')
                        ->searchable()
                        ->nullable()
                        ->options(function ($get, $record) {
                            $phone = $get('phone');
                            $email = $get('email');
                            
                            $query = User::query()
                                ->where('role', UserType::USER->value);

                            $userFoundByPhone = false;

                            if (!empty($phone)) {
                                $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
                                $lastDigits = $cleanPhone ? substr($cleanPhone, -9) : null;
                                
                                $phoneQuery = User::query()
                                    ->where('role', UserType::USER->value)
                                    ->where(function ($q) use ($phone, $lastDigits) {
                                        $q->where('phone', 'like', "%{$phone}%");
                                        if ($lastDigits && strlen($lastDigits) >= 7) {
                                            $q->orWhere('phone', 'like', "%{$lastDigits}%");
                                        }
                                    });

                                if ($phoneQuery->exists()) {
                                    $userFoundByPhone = true;
                                    $query->where(function ($q) use ($phone, $lastDigits) {
                                        $q->where('phone', 'like', "%{$phone}%");
                                        if ($lastDigits && strlen($lastDigits) >= 7) {
                                            $q->orWhere('phone', 'like', "%{$lastDigits}%");
                                        }
                                    });
                                }
                            }

                            if (!$userFoundByPhone && !empty($email)) {
                                $emailQuery = User::query()
                                    ->where('role', UserType::USER->value)
                                    ->where('email', $email);

                                if ($emailQuery->exists()) {
                                    $query->where('email', $email);
                                } else {
                                    $query->whereRaw('1 = 0');
                                }
                            } elseif (!$userFoundByPhone) {
                                $query->whereRaw('1 = 0');
                            }

                            if ($record?->user_id) {
                                $query->orWhere('id', $record->user_id);
                            }

                            return $query
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (User $user) => [
                                    $user->id => "{$user->name} ({$user->email})",
                                ]);
                        })
                        ->getSearchResultsUsing(fn (string $search) => User::query()
                            ->where('role', UserType::USER->value)
                            ->where(function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%")
                                  ->orWhere('email', 'like', "%{$search}%")
                                  ->orWhere('phone', 'like', "%{$search}%");
                            })
                            ->limit(50)
                            ->get()
                            ->mapWithKeys(fn (User $user) => [
                                $user->id => "{$user->name} ({$user->email})",
                            ])
                        )
                        ->getOptionLabelUsing(function ($value) {
                            $user = User::find($value);
                            return $user ? "{$user->name} ({$user->email})" : null;
                        })
                        ->helperText('مطلوب لمشاركة كشف الحساب — يجب أن يكون العميل مسجّلاً كمستخدم في النظام'),
                    Toggle::make('is_active')->label('نشط')->default(true),
                ])
                ->columns(3)
                ->columnSpanFull(),
        ]);
    }
}
