<?php

namespace App\Filament\Pages\Auth;

use App\Enums\UserType;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class Register extends BaseRegister
{
    public function mount(): void
    {
        parent::mount();

        $type = request()->query('type');

        if ($type === 'merchant') {
            $this->form->fill([
                'role' => UserType::MERCHANT->value,
            ]);
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Radio::make('role')
                    ->label('نوع الحساب')
                    ->options(UserType::registrationOptions())
                    ->default(UserType::USER->value)
                    ->required()
                    ->live(),

                $this->getNameFormComponent(),

                TextInput::make('business_name')
                    ->label('اسم النشاط التجاري')
                    ->maxLength(255)
                    ->visible(fn (Get $get): bool => $get('role') === UserType::MERCHANT->value)
                    ->required(fn (Get $get): bool => $get('role') === UserType::MERCHANT->value),

                TextInput::make('business_activity')
                    ->label('نوع النشاط')
                    ->maxLength(255)
                    ->visible(fn (Get $get): bool => $get('role') === UserType::MERCHANT->value),

                TextInput::make('business_location')
                    ->label('موقع النشاط')
                    ->maxLength(255)
                    ->visible(fn (Get $get): bool => $get('role') === UserType::MERCHANT->value),

                TextInput::make('tax_number')
                    ->label('الرقم الضريبي')
                    ->maxLength(50)
                    ->visible(fn (Get $get): bool => $get('role') === UserType::MERCHANT->value),

                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }

    protected function mutateFormDataBeforeRegister(array $data): array
    {
        $data['role'] = $data['role'] ?? UserType::USER->value;

        if ($data['role'] !== UserType::MERCHANT->value) {
            unset($data['business_name'], $data['business_activity'], $data['business_location'], $data['tax_number']);
        }

        return $data;
    }

    protected function handleRegistration(array $data): Model
    {
        return parent::handleRegistration($data);
    }
}
