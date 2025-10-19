<?php

namespace App\Helpers;

use App\Enums\ProductUnit;

class UnitScale
{
    /**
     * Unit scales for different categories
     */
    private static array $scales = [
        'weight' => [
            'micro' => [
                'unit' => ProductUnit::GRAM->value,
                'factor' => 0.001,
                'label' => 'مليغرام',
                'symbol' => 'mg',
            ],
            'small' => [
                'unit' => ProductUnit::GRAM->value,
                'factor' => 1,
                'label' => 'غرام',
                'symbol' => 'g',
            ],
            'medium' => [
                'unit' => ProductUnit::KILOGRAM->value,
                'factor' => 1,
                'label' => 'كيلوغرام',
                'symbol' => 'kg',
            ],
            'large' => [
                'unit' => ProductUnit::KILOGRAM->value,
                'factor' => 1000,
                'label' => 'طن',
                'symbol' => 't',
            ],
        ],
        'volume' => [
            'small' => [
                'unit' => ProductUnit::LITER->value,
                'factor' => 0.001,
                'label' => 'مليلتر',
                'symbol' => 'ml',
            ],
            'medium' => [
                'unit' => ProductUnit::LITER->value,
                'factor' => 1,
                'label' => 'لتر',
                'symbol' => 'L',
            ],
            'large' => [
                'unit' => ProductUnit::LITER->value,
                'factor' => 1000,
                'label' => 'كيلولتر',
                'symbol' => 'kL',
            ],
        ],
        'length' => [
            'small' => [
                'unit' => ProductUnit::METER->value,
                'factor' => 0.001,
                'label' => 'مليمتر',
                'symbol' => 'mm',
            ],
            'medium' => [
                'unit' => ProductUnit::METER->value,
                'factor' => 1,
                'label' => 'متر',
                'symbol' => 'm',
            ],
            'large' => [
                'unit' => ProductUnit::METER->value,
                'factor' => 1000,
                'label' => 'كيلومتر',
                'symbol' => 'km',
            ],
        ],
        'count' => [
            'single' => [
                'unit' => ProductUnit::PIECE->value,
                'factor' => 1,
                'label' => 'قطعة',
                'symbol' => 'pcs',
            ],
            'dozen' => [
                'unit' => ProductUnit::PIECE->value,
                'factor' => 12,
                'label' => 'دزينة',
                'symbol' => 'doz',
            ],
            'gross' => [
                'unit' => ProductUnit::PIECE->value,
                'factor' => 144,
                'label' => 'جروس',
                'symbol' => 'gross',
            ],
        ],
    ];

    /**
     * Get scale for a specific unit
     */
    public static function getScale(string $unit): array
    {
        $category = UnitCalculator::getUnitCategory($unit);
        return self::$scales[$category] ?? [];
    }

    /**
     * Get all available scales
     */
    public static function getAllScales(): array
    {
        return self::$scales;
    }

    /**
     * Get scale by category
     */
    public static function getScaleByCategory(string $category): array
    {
        return self::$scales[$category] ?? [];
    }

    /**
     * Get scale options for forms
     */
    public static function getScaleOptions(string $category): array
    {
        $scales = self::getScaleByCategory($category);
        $options = [];

        foreach ($scales as $key => $scale) {
            $options[$key] = $scale['label'] . ' (' . $scale['symbol'] . ')';
        }

        return $options;
    }

    /**
     * Get scale by key
     */
    public static function getScaleByKey(string $category, string $key): ?array
    {
        return self::$scales[$category][$key] ?? null;
    }

    /**
     * Convert quantity using scale
     */
    public static function convertWithScale(float $quantity, string $fromScale, string $toScale, string $category): float
    {
        $fromScaleData = self::getScaleByKey($category, $fromScale);
        $toScaleData = self::getScaleByKey($category, $toScale);

        if (!$fromScaleData || !$toScaleData) {
            throw new \InvalidArgumentException("Invalid scale for category: {$category}");
        }

        $fromFactor = $fromScaleData['factor'];
        $toFactor = $toScaleData['factor'];

        return ($quantity * $fromFactor) / $toFactor;
    }

    /**
     * Get recommended scale for quantity
     */
    public static function getRecommendedScale(float $quantity, string $category): string
    {
        $scales = self::getScaleByCategory($category);

        if (empty($scales)) {
            return 'medium';
        }

        // Find the most appropriate scale based on quantity
        foreach ($scales as $key => $scale) {
            if ($quantity >= $scale['factor'] && $quantity < ($scale['factor'] * 1000)) {
                return $key;
            }
        }

        // Default to medium scale
        return 'medium';
    }

    /**
     * Format quantity with scale
     */
    public static function formatWithScale(float $quantity, string $scale, string $category): string
    {
        $scaleData = self::getScaleByKey($category, $scale);

        if (!$scaleData) {
            return number_format($quantity, 2);
        }

        $convertedQuantity = $quantity * $scaleData['factor'];
        return number_format($convertedQuantity, 2) . ' ' . $scaleData['label'];
    }

    /**
     * Get scale conversion factor
     */
    public static function getScaleFactor(string $category, string $scale): float
    {
        $scaleData = self::getScaleByKey($category, $scale);
        return $scaleData['factor'] ?? 1;
    }

    /**
     * Get scale label
     */
    public static function getScaleLabel(string $category, string $scale): string
    {
        $scaleData = self::getScaleByKey($category, $scale);
        return $scaleData['label'] ?? $scale;
    }

    /**
     * Get scale symbol
     */
    public static function getScaleSymbol(string $category, string $scale): string
    {
        $scaleData = self::getScaleByKey($category, $scale);
        return $scaleData['symbol'] ?? $scale;
    }

    /**
     * Check if scale exists
     */
    public static function scaleExists(string $category, string $scale): bool
    {
        return isset(self::$scales[$category][$scale]);
    }

    /**
     * Get all scale categories
     */
    public static function getCategories(): array
    {
        return array_keys(self::$scales);
    }

    /**
     * Get scale info
     */
    public static function getScaleInfo(string $category, string $scale): array
    {
        $scaleData = self::getScaleByKey($category, $scale);

        if (!$scaleData) {
            return [];
        }

        return [
            'category' => $category,
            'scale' => $scale,
            'unit' => $scaleData['unit'],
            'factor' => $scaleData['factor'],
            'label' => $scaleData['label'],
            'symbol' => $scaleData['symbol'],
        ];
    }
}
