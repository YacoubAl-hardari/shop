<?php

namespace App\Helpers;

use App\Enums\ProductUnit;

class UnitCalculator
{
    /**
     * Unit conversion rates to base units
     * Base units: gram (g), liter (L), meter (m), piece (pcs)
     */
    private static array $conversionRates = [
        // Weight units (base: gram)
        ProductUnit::GRAM->value => 1,
        ProductUnit::KILOGRAM->value => 1000,
        ProductUnit::KILOGRAM_GRAM->value => 1000,
        
        // Volume units (base: liter)
        ProductUnit::LITER->value => 1,
        
        // Length units (base: meter)
        ProductUnit::METER->value => 1,
        
        // Count units (base: piece)
        ProductUnit::PIECE->value => 1,
        ProductUnit::ITEM->value => 1,
        ProductUnit::BOX->value => 1,
        ProductUnit::PACK->value => 1,
    ];

    /**
     * Unit categories for grouping
     */
    private static array $unitCategories = [
        'weight' => [
            ProductUnit::GRAM->value,
            ProductUnit::KILOGRAM->value,
            ProductUnit::KILOGRAM_GRAM->value,
        ],
        'volume' => [
            ProductUnit::LITER->value,
        ],
        'length' => [
            ProductUnit::METER->value,
        ],
        'count' => [
            ProductUnit::PIECE->value,
            ProductUnit::ITEM->value,
            ProductUnit::BOX->value,
            ProductUnit::PACK->value,
        ],
    ];

    /**
     * Calculate total price based on unit type and quantity
     */
    public static function calculateTotalPrice(float $quantity, float $unitPrice, string $unit): float
    {
        $unitEnum = ProductUnit::fromValue($unit);
        
        if (!$unitEnum) {
            throw new \InvalidArgumentException("Invalid unit: {$unit}");
        }

        // For count-based units, simple multiplication
        if ($unitEnum->isCount()) {
            return $quantity * $unitPrice;
        }

        // For weight, volume, and length units, apply conversion factor
        $conversionFactor = self::getConversionFactor($unit);
        $baseQuantity = $quantity * $conversionFactor;
        
        return $baseQuantity * $unitPrice;
    }

    /**
     * Get conversion factor for a unit
     */
    public static function getConversionFactor(string $unit): float
    {
        return self::$conversionRates[$unit] ?? 1;
    }

    /**
     * Convert quantity from one unit to another
     */
    public static function convertQuantity(float $quantity, string $fromUnit, string $toUnit): float
    {
        if ($fromUnit === $toUnit) {
            return $quantity;
        }

        $fromCategory = self::getUnitCategory($fromUnit);
        $toCategory = self::getUnitCategory($toUnit);

        // Can only convert within the same category
        if ($fromCategory !== $toCategory) {
            throw new \InvalidArgumentException("Cannot convert between different unit categories: {$fromCategory} to {$toCategory}");
        }

        $fromFactor = self::getConversionFactor($fromUnit);
        $toFactor = self::getConversionFactor($toUnit);

        return ($quantity * $fromFactor) / $toFactor;
    }

    /**
     * Get unit category
     */
    public static function getUnitCategory(string $unit): string
    {
        foreach (self::$unitCategories as $category => $units) {
            if (in_array($unit, $units)) {
                return $category;
            }
        }

        throw new \InvalidArgumentException("Unknown unit category for unit: {$unit}");
    }

    /**
     * Get all units in a category
     */
    public static function getUnitsInCategory(string $category): array
    {
        return self::$unitCategories[$category] ?? [];
    }

    /**
     * Check if two units are compatible (same category)
     */
    public static function areUnitsCompatible(string $unit1, string $unit2): bool
    {
        try {
            return self::getUnitCategory($unit1) === self::getUnitCategory($unit2);
        } catch (\InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * Get unit weight for sorting/priority
     */
    public static function getUnitWeight(string $unit): float
    {
        $unitEnum = ProductUnit::fromValue($unit);
        
        if (!$unitEnum) {
            return 0;
        }

        // Weight units (higher weight = smaller unit)
        if ($unitEnum->isWeight()) {
            return match($unit) {
                ProductUnit::GRAM->value => 1000,
                ProductUnit::KILOGRAM->value => 1,
                ProductUnit::KILOGRAM_GRAM->value => 1,
                default => 1,
            };
        }

        // Volume units
        if ($unitEnum->isVolume()) {
            return match($unit) {
                ProductUnit::LITER->value => 1,
                default => 1,
            };
        }

        // Length units
        if ($unitEnum->isLength()) {
            return match($unit) {
                ProductUnit::METER->value => 1,
                default => 1,
            };
        }

        // Count units
        if ($unitEnum->isCount()) {
            return match($unit) {
                ProductUnit::PIECE->value => 1,
                ProductUnit::ITEM->value => 1,
                ProductUnit::BOX->value => 0.1,
                ProductUnit::PACK->value => 0.1,
                default => 1,
            };
        }

        return 1;
    }

    /**
     * Get unit display name with symbol
     */
    public static function getUnitDisplayName(string $unit): string
    {
        $unitEnum = ProductUnit::fromValue($unit);
        
        if (!$unitEnum) {
            return $unit;
        }

        return $unitEnum->arabicLabel() . ' (' . $unitEnum->symbol() . ')';
    }

    /**
     * Calculate unit price per base unit
     */
    public static function calculateBaseUnitPrice(float $quantity, float $totalPrice, string $unit): float
    {
        $conversionFactor = self::getConversionFactor($unit);
        $baseQuantity = $quantity * $conversionFactor;
        
        return $baseQuantity > 0 ? $totalPrice / $baseQuantity : 0;
    }

    /**
     * Get recommended units for a category
     */
    public static function getRecommendedUnits(string $category): array
    {
        return match($category) {
            'weight' => [
                ProductUnit::KILOGRAM->value,
                ProductUnit::GRAM->value,
            ],
            'volume' => [
                ProductUnit::LITER->value,
            ],
            'length' => [
                ProductUnit::METER->value,
            ],
            'count' => [
                ProductUnit::PIECE->value,
                ProductUnit::ITEM->value,
            ],
            default => [],
        };
    }

    /**
     * Format quantity with unit
     */
    public static function formatQuantity(float $quantity, string $unit): string
    {
        $unitEnum = ProductUnit::fromValue($unit);
        
        if (!$unitEnum) {
            return $quantity . ' ' . $unit;
        }

        return number_format($quantity, 2) . ' ' . $unitEnum->arabicLabel();
    }

    /**
     * Get unit conversion info
     */
    public static function getUnitConversionInfo(string $unit): array
    {
        $unitEnum = ProductUnit::fromValue($unit);
        
        if (!$unitEnum) {
            return [];
        }

        return [
            'unit' => $unit,
            'category' => self::getUnitCategory($unit),
            'conversion_factor' => self::getConversionFactor($unit),
            'symbol' => $unitEnum->symbol(),
            'arabic_label' => $unitEnum->arabicLabel(),
            'english_label' => $unitEnum->label(),
            'is_weight' => $unitEnum->isWeight(),
            'is_volume' => $unitEnum->isVolume(),
            'is_length' => $unitEnum->isLength(),
            'is_count' => $unitEnum->isCount(),
        ];
    }
}
