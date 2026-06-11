<?php

namespace App\Filament\Schemas;

use App\Enums\MerchantPaymentAccountType;
use App\Models\MerchantPaymentAccount;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;

class PaymentDetailsSchema
{
    public static function methodSelect(string $field = 'payment_method', bool $required = true): Select
    {
        $select = Select::make($field)
            ->label('طريقة الدفع')
            ->options([
                'cash' => 'نقد',
                'card' => 'بطاقة',
                'bank_transfer' => 'تحويل بنكي',
            ])
            ->default('cash')
            ->live();

        return $required ? $select->required() : $select;
    }

    public static function accountSelect(
        string $methodField = 'payment_method',
        string $accountField = 'merchant_payment_account_id',
    ): Select {
        return Select::make($accountField)
            ->label(fn (Get $get): string => match ($get($methodField)) {
                'bank_transfer' => 'البنك',
                'card' => 'البطاقة / المحفظة',
                default => 'الحساب',
            })
            ->options(fn (Get $get): array => self::accountOptions($get($methodField)))
            ->searchable()
            ->preload()
            ->live()
            ->visible(fn (Get $get): bool => self::requiresAccount($get($methodField)))
            ->required(fn (Get $get): bool => self::requiresAccount($get($methodField)));
    }

    public static function referenceInput(
        string $methodField = 'payment_method',
        string $field = 'payment_reference',
    ): TextInput {
        return TextInput::make($field)
            ->label(fn (Get $get): string => match ($get($methodField)) {
                'bank_transfer' => 'رقم الحوالة',
                'card' => 'رقم العملية',
                default => 'مرجع الدفع',
            })
            ->visible(fn (Get $get): bool => self::requiresAccount($get($methodField)))
            ->required(fn (Get $get): bool => self::requiresAccount($get($methodField)));
    }

    public static function accountPreview(
        string $methodField = 'payment_method',
        string $accountField = 'merchant_payment_account_id',
    ): Placeholder {
        return Placeholder::make('payment_account_preview')
            ->label('تفاصيل الحساب')
            ->visible(fn (Get $get): bool => self::requiresAccount($get($methodField)) && filled($get($accountField)))
            ->content(function (Get $get) use ($accountField): string {
                $account = MerchantPaymentAccount::find($get($accountField));

                if (! $account) {
                    return '—';
                }

                return "{$account->name}\n{$account->account_number}";
            });
    }

    public static function requiresAccount(?string $method): bool
    {
        return in_array($method, ['card', 'bank_transfer'], true);
    }

    /**
     * @return array<int|string, string>
     */
    public static function accountOptions(?string $method, ?int $teamId = null): array
    {
        if (! self::requiresAccount($method)) {
            return [];
        }

        $type = $method === 'bank_transfer'
            ? MerchantPaymentAccountType::BANK
            : MerchantPaymentAccountType::CARD;

        $query = $teamId !== null
            ? MerchantPaymentAccount::withoutGlobalScopes()->where('team_id', $teamId)
            : MerchantPaymentAccount::query();

        return $query
            ->where('is_active', true)
            ->where('type', $type)
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (MerchantPaymentAccount $account) => [
                $account->id => $account->displayLabel(),
            ])
            ->all();
    }

    public static function accountSelectForTeam(
        int $teamId,
        string $methodField = 'payment_method',
        string $accountField = 'merchant_payment_account_id',
    ): Select {
        return Select::make($accountField)
            ->label(fn (Get $get): string => match ($get($methodField)) {
                'bank_transfer' => 'البنك',
                'card' => 'البطاقة / المحفظة',
                default => 'الحساب',
            })
            ->options(fn (Get $get): array => self::accountOptions($get($methodField), $teamId))
            ->searchable()
            ->preload()
            ->live()
            ->visible(fn (Get $get): bool => self::requiresAccount($get($methodField)))
            ->required(fn (Get $get): bool => self::requiresAccount($get($methodField)));
    }

    public static function accountPreviewForTeam(
        int $teamId,
        string $methodField = 'payment_method',
        string $accountField = 'merchant_payment_account_id',
    ): Placeholder {
        return Placeholder::make('payment_account_preview')
            ->label('تفاصيل الحساب')
            ->visible(fn (Get $get): bool => self::requiresAccount($get($methodField)) && filled($get($accountField)))
            ->content(function (Get $get) use ($accountField, $teamId): string {
                $account = MerchantPaymentAccount::withoutGlobalScopes()
                    ->where('team_id', $teamId)
                    ->find($get($accountField));

                if (! $account) {
                    return '—';
                }

                return "{$account->name}\n{$account->account_number}";
            });
    }
}
