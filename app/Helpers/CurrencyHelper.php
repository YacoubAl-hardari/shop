<?php

namespace App\Helpers;

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;

class CurrencyHelper
{
    /**
     * Get all supported currencies with country, name, symbol, and code.
     */
    public static function getCurrencies(): array
    {
        return [
            'SAR' => [
                'code' => 'SAR',
                'symbol' => 'ر.س',
                'name' => 'ريال سعودي',
                'country' => 'المملكة العربية السعودية',
            ],
            'YER' => [
                'code' => 'YER',
                'symbol' => 'ر.ي',
                'name' => 'ريال يمني',
                'country' => 'اليمن',
            ],
            'EGP' => [
                'code' => 'EGP',
                'symbol' => 'ج.م',
                'name' => 'جنيه مصري',
                'country' => 'مصر',
            ],
            'USD' => [
                'code' => 'USD',
                'symbol' => '$',
                'name' => 'دولار أمريكي',
                'country' => 'الولايات المتحدة',
            ],
        ];
    }

    /**
     * Get options list for dropdown selects.
     */
    public static function getOptions(): array
    {
        $options = [];
        foreach (self::getCurrencies() as $code => $info) {
            $options[$code] = "{$info['symbol']} - {$info['name']} ({$info['country']})";
        }
        return $options;
    }

    /**
     * Retrieve active merchant (tenant team) currency.
     */
    public static function getMerchantCurrency(): string
    {
        try {
            $tenant = Filament::getTenant();
            if ($tenant && !empty($tenant->currency)) {
                return $tenant->currency;
            }
        } catch (\Throwable $e) {}

        return 'SAR';
    }

    /**
     * Retrieve active customer (authenticated user) currency.
     */
    public static function getClientCurrency(): string
    {
        try {
            $user = Auth::user();
            if ($user && !empty($user->currency)) {
                return $user->currency;
            }
        } catch (\Throwable $e) {}

        return 'SAR';
    }

    /**
     * Get currency code depending on the panel context.
     */
    public static function getCode(string $customCode = null): string
    {
        if ($customCode) {
            return $customCode;
        }

        try {
            if (Filament::getTenant()) {
                return self::getMerchantCurrency();
            }
        } catch (\Throwable $e) {}

        return self::getClientCurrency();
    }

    /**
     * Get currency symbol depending on the context.
     */
    public static function getSymbol(string $code = null): string
    {
        $resolvedCode = $code ?: self::getCode();
        $currencies = self::getCurrencies();

        return $currencies[$resolvedCode]['symbol'] ?? 'ر.س';
    }

    /**
     * Format a numerical value with the dynamic currency.
     */
    public static function format(float $amount, string $code = null): string
    {
        $symbol = self::getSymbol($code);
        return number_format($amount, 2) . ' ' . $symbol;
    }
}
