<?php

namespace App\Helpers;

use App\Enums\ProductUnit;

class UnitBalance
{
    /**
     * Unit balance configurations
     */
    private static array $balances = [
        'weight' => [
            'base_unit' => ProductUnit::GRAM->value,
            'precision' => 3,
            'min_value' => 0.001,
            'max_value' => 1000000,
            'default_scale' => 'medium',
        ],
        'volume' => [
            'base_unit' => ProductUnit::LITER->value,
            'precision' => 3,
            'min_value' => 0.001,
            'max_value' => 10000,
            'default_scale' => 'medium',
        ],
        'length' => [
            'base_unit' => ProductUnit::METER->value,
            'precision' => 2,
            'min_value' => 0.01,
            'max_value' => 10000,
            'default_scale' => 'medium',
        ],
        'count' => [
            'base_unit' => ProductUnit::PIECE->value,
            'precision' => 0,
            'min_value' => 1,
            'max_value' => 1000000,
            'default_scale' => 'single',
        ],
    ];

    /**
     * Get balance configuration for a category
     */
    public static function getBalance(string $category): array
    {
        return self::$balances[$category] ?? [];
    }

    /**
     * Get all balance configurations
     */
    public static function getAllBalances(): array
    {
        return self::$balances;
    }

    /**
     * Get base unit for a category
     */
    public static function getBaseUnit(string $category): string
    {
        $balance = self::getBalance($category);
        return $balance['base_unit'] ?? ProductUnit::PIECE->value;
    }

    /**
     * Get precision for a category
     */
    public static function getPrecision(string $category): int
    {
        $balance = self::getBalance($category);
        return $balance['precision'] ?? 2;
    }

    /**
     * Get minimum value for a category
     */
    public static function getMinValue(string $category): float
    {
        $balance = self::getBalance($category);
        return $balance['min_value'] ?? 0.01;
    }

    /**
     * Get maximum value for a category
     */
    public static function getMaxValue(string $category): float
    {
        $balance = self::getMaxValue($category);
        return $balance['max_value'] ?? 1000;
    }

    /**
     * Get default scale for a category
     */
    public static function getDefaultScale(string $category): string
    {
        $balance = self::getBalance($category);
        return $balance['default_scale'] ?? 'medium';
    }

    /**
     * Validate quantity for a category
     */
    public static function validateQuantity(float $quantity, string $category): bool
    {
        $minValue = self::getMinValue($category);
        $maxValue = self::getMaxValue($category);

        return $quantity >= $minValue && $quantity <= $maxValue;
    }

    /**
     * Format quantity with balance settings
     */
    public static function formatQuantity(float $quantity, string $category): string
    {
        $precision = self::getPrecision($category);
        $baseUnit = self::getBaseUnit($category);
        
        $unitEnum = ProductUnit::fromValue($baseUnit);
        $unitLabel = $unitEnum ? $unitEnum->arabicLabel() : $baseUnit;

        return number_format($quantity, $precision) . ' ' . $unitLabel;
    }

    /**
     * Get balance info for a category
     */
    public static function getBalanceInfo(string $category): array
    {
        $balance = self::getBalance($category);

        if (empty($balance)) {
            return [];
        }

        return [
            'category' => $category,
            'base_unit' => $balance['base_unit'],
            'precision' => $balance['precision'],
            'min_value' => $balance['min_value'],
            'max_value' => $balance['max_value'],
            'default_scale' => $balance['default_scale'],
            'unit_label' => ProductUnit::fromValue($balance['base_unit'])?->arabicLabel(),
            'unit_symbol' => ProductUnit::fromValue($balance['base_unit'])?->symbol(),
        ];
    }

    /**
     * Calculate balance for a unit
     */
    public static function calculateBalance(float $quantity, string $unit): array
    {
        $category = UnitCalculator::getUnitCategory($unit);
        $balance = self::getBalance($category);

        if (empty($balance)) {
            return [];
        }

        $baseUnit = $balance['base_unit'];
        $conversionFactor = UnitCalculator::getConversionFactor($unit);
        $baseQuantity = $quantity * $conversionFactor;

        return [
            'original_quantity' => $quantity,
            'original_unit' => $unit,
            'base_quantity' => $baseQuantity,
            'base_unit' => $baseUnit,
            'conversion_factor' => $conversionFactor,
            'category' => $category,
            'precision' => $balance['precision'],
            'formatted' => self::formatQuantity($baseQuantity, $category),
        ];
    }

    /**
     * Get balance summary for all categories
     */
    public static function getBalanceSummary(): array
    {
        $summary = [];

        foreach (self::$balances as $category => $balance) {
            $summary[$category] = self::getBalanceInfo($category);
        }

        return $summary;
    }

    /**
     * Check if category has balance configuration
     */
    public static function hasBalance(string $category): bool
    {
        return isset(self::$balances[$category]);
    }

    /**
     * Get recommended unit for a category
     */
    public static function getRecommendedUnit(string $category): string
    {
        $balance = self::getBalance($category);
        return $balance['base_unit'] ?? ProductUnit::PIECE->value;
    }

    /**
     * Get balance categories
     */
    public static function getCategories(): array
    {
        return array_keys(self::$balances);
    }

    /**
     * Update balance configuration
     */
    public static function updateBalance(string $category, array $config): void
    {
        self::$balances[$category] = array_merge(
            self::$balances[$category] ?? [],
            $config
        );
    }

    /**
     * Reset balance configuration
     */
    public static function resetBalance(string $category): void
    {
        unset(self::$balances[$category]);
    }

    /**
     * Get balance validation rules
     */
    public static function getValidationRules(string $category): array
    {
        $balance = self::getBalance($category);

        if (empty($balance)) {
            return ['numeric', 'min:0'];
        }

        return [
            'numeric',
            'min:' . $balance['min_value'],
            'max:' . $balance['max_value'],
        ];
    }
}
