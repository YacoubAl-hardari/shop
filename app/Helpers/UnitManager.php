<?php

namespace App\Helpers;

use App\Enums\ProductUnit;

class UnitManager
{
    /**
     * Calculate total price with unit conversion
     */
    public static function calculateTotalPrice(float $quantity, float $unitPrice, string $unit): float
    {
        return UnitCalculator::calculateTotalPrice($quantity, $unitPrice, $unit);
    }

    /**
     * Get unit information
     */
    public static function getUnitInfo(string $unit): array
    {
        return UnitCalculator::getUnitConversionInfo($unit);
    }

    /**
     * Get unit category
     */
    public static function getUnitCategory(string $unit): string
    {
        return UnitCalculator::getUnitCategory($unit);
    }

    /**
     * Get all units in a category
     */
    public static function getUnitsInCategory(string $category): array
    {
        return UnitCalculator::getUnitsInCategory($category);
    }

    /**
     * Get unit options for forms
     */
    public static function getUnitOptions(string $category = null): array
    {
        if ($category) {
            $units = self::getUnitsInCategory($category);
            $options = [];
            
            foreach ($units as $unit) {
                $unitEnum = ProductUnit::fromValue($unit);
                if ($unitEnum) {
                    $options[$unit] = $unitEnum->arabicLabel();
                }
            }
            
            return $options;
        }

        return ProductUnit::arabicOptions();
    }

    /**
     * Get scale options for a category
     */
    public static function getScaleOptions(string $category): array
    {
        return UnitScale::getScaleOptions($category);
    }

    /**
     * Get balance information for a category
     */
    public static function getBalanceInfo(string $category): array
    {
        return UnitBalance::getBalanceInfo($category);
    }

    /**
     * Format quantity with unit
     */
    public static function formatQuantity(float $quantity, string $unit): string
    {
        return UnitCalculator::formatQuantity($quantity, $unit);
    }

    /**
     * Format quantity with scale
     */
    public static function formatWithScale(float $quantity, string $scale, string $category): string
    {
        return UnitScale::formatWithScale($quantity, $scale, $category);
    }

    /**
     * Convert quantity between units
     */
    public static function convertQuantity(float $quantity, string $fromUnit, string $toUnit): float
    {
        return UnitCalculator::convertQuantity($quantity, $fromUnit, $toUnit);
    }

    /**
     * Convert quantity using scale
     */
    public static function convertWithScale(float $quantity, string $fromScale, string $toScale, string $category): float
    {
        return UnitScale::convertWithScale($quantity, $fromScale, $toScale, $category);
    }

    /**
     * Get recommended unit for a category
     */
    public static function getRecommendedUnit(string $category): string
    {
        return UnitBalance::getRecommendedUnit($category);
    }

    /**
     * Get recommended scale for quantity
     */
    public static function getRecommendedScale(float $quantity, string $category): string
    {
        return UnitScale::getRecommendedScale($quantity, $category);
    }

    /**
     * Validate quantity for a category
     */
    public static function validateQuantity(float $quantity, string $category): bool
    {
        return UnitBalance::validateQuantity($quantity, $category);
    }

    /**
     * Get validation rules for a category
     */
    public static function getValidationRules(string $category): array
    {
        return UnitBalance::getValidationRules($category);
    }

    /**
     * Calculate balance for a unit
     */
    public static function calculateBalance(float $quantity, string $unit): array
    {
        return UnitBalance::calculateBalance($quantity, $unit);
    }

    /**
     * Get unit display name with symbol
     */
    public static function getUnitDisplayName(string $unit): string
    {
        return UnitCalculator::getUnitDisplayName($unit);
    }

    /**
     * Get unit weight for sorting
     */
    public static function getUnitWeight(string $unit): float
    {
        return UnitCalculator::getUnitWeight($unit);
    }

    /**
     * Check if units are compatible
     */
    public static function areUnitsCompatible(string $unit1, string $unit2): bool
    {
        return UnitCalculator::areUnitsCompatible($unit1, $unit2);
    }

    /**
     * Get all categories
     */
    public static function getCategories(): array
    {
        return UnitBalance::getCategories();
    }

    /**
     * Get all scales
     */
    public static function getAllScales(): array
    {
        return UnitScale::getAllScales();
    }

    /**
     * Get all balances
     */
    public static function getAllBalances(): array
    {
        return UnitBalance::getAllBalances();
    }

    /**
     * Get comprehensive unit information
     */
    public static function getComprehensiveUnitInfo(string $unit): array
    {
        $unitInfo = self::getUnitInfo($unit);
        $category = $unitInfo['category'] ?? 'unknown';
        
        return [
            'unit' => $unitInfo,
            'category' => $category,
            'balance' => self::getBalanceInfo($category),
            'scales' => self::getScaleOptions($category),
            'recommended_unit' => self::getRecommendedUnit($category),
            'validation_rules' => self::getValidationRules($category),
            'compatible_units' => self::getUnitsInCategory($category),
        ];
    }

    /**
     * Get unit selection data for forms
     */
    public static function getUnitSelectionData(): array
    {
        $data = [];
        
        foreach (self::getCategories() as $category) {
            $data[$category] = [
                'units' => self::getUnitOptions($category),
                'scales' => self::getScaleOptions($category),
                'balance' => self::getBalanceInfo($category),
                'recommended_unit' => self::getRecommendedUnit($category),
                'validation_rules' => self::getValidationRules($category),
            ];
        }
        
        return $data;
    }

    /**
     * Process unit selection and return calculated values
     */
    public static function processUnitSelection(string $unit, float $quantity = 1): array
    {
        $unitInfo = self::getComprehensiveUnitInfo($unit);
        $category = $unitInfo['category'];
        
        return [
            'unit' => $unit,
            'category' => $category,
            'quantity' => $quantity,
            'formatted_quantity' => self::formatQuantity($quantity, $unit),
            'balance' => self::calculateBalance($quantity, $unit),
            'recommended_scale' => self::getRecommendedScale($quantity, $category),
            'validation' => [
                'is_valid' => self::validateQuantity($quantity, $category),
                'rules' => self::getValidationRules($category),
            ],
            'compatible_units' => $unitInfo['compatible_units'],
            'scales' => $unitInfo['scales'],
        ];
    }
}
