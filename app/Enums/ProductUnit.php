<?php

namespace App\Enums;

enum ProductUnit: string
{
    case PIECE = 'حبه';
    case KILOGRAM = 'كيلو';
    case METER = 'متر';
    case BOX = 'علبة';
    case ITEM = 'قطعة';
    case PACK = 'باكت';
    case LITER = 'لتر';
    case GRAM = 'غرام';
    case KILOGRAM_GRAM = 'كيلو غرام';

    /**
     * Get all enum values as an array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get the default unit
     */
    public static function default(): self
    {
        return self::PIECE;
    }

    /**
     * Get the label for the unit
     */
    public function label(): string
    {
        return match($this) {
            self::PIECE => 'Piece',
            self::KILOGRAM => 'Kilogram',
            self::METER => 'Meter',
            self::BOX => 'Box',
            self::ITEM => 'Item',
            self::PACK => 'Pack',
            self::LITER => 'Liter',
            self::GRAM => 'Gram',
            self::KILOGRAM_GRAM => 'Kilogram Gram',
        };
    }

    /**
     * Get the Arabic label for the unit
     */
    public function arabicLabel(): string
    {
        return match($this) {
            self::PIECE => 'حبه',
            self::KILOGRAM => 'كيلو',
            self::METER => 'متر',
            self::BOX => 'علبة',
            self::ITEM => 'قطعة',
            self::PACK => 'باكت',
            self::LITER => 'لتر',
            self::GRAM => 'غرام',
            self::KILOGRAM_GRAM => 'كيلو غرام',
        };
    }

    /**
     * Get the unit symbol/abbreviation
     */
    public function symbol(): string
    {
        return match($this) {
            self::PIECE => 'pcs',
            self::KILOGRAM => 'kg',
            self::METER => 'm',
            self::BOX => 'box',
            self::ITEM => 'item',
            self::PACK => 'pack',
            self::LITER => 'L',
            self::GRAM => 'g',
            self::KILOGRAM_GRAM => 'kg',
        };
    }

    /**
     * Check if the unit is weight-based
     */
    public function isWeight(): bool
    {
        return in_array($this, [
            self::KILOGRAM,
            self::GRAM,
            self::KILOGRAM_GRAM,
        ]);
    }

    /**
     * Check if the unit is volume-based
     */
    public function isVolume(): bool
    {
        return in_array($this, [
            self::LITER,
        ]);
    }

    /**
     * Check if the unit is length-based
     */
    public function isLength(): bool
    {
        return in_array($this, [
            self::METER,
        ]);
    }

    /**
     * Check if the unit is count-based
     */
    public function isCount(): bool
    {
        return in_array($this, [
            self::PIECE,
            self::BOX,
            self::ITEM,
            self::PACK,
        ]);
    }

    /**
     * Get unit by value
     */
    public static function fromValue(string $value): ?self
    {
        return self::tryFrom($value);
    }

    /**
     * Get all units as key-value pairs for forms
     */
    public static function options(): array
    {
        return [
            self::PIECE->value => self::PIECE->label(),
            self::KILOGRAM->value => self::KILOGRAM->label(),
            self::METER->value => self::METER->label(),
            self::BOX->value => self::BOX->label(),
            self::ITEM->value => self::ITEM->label(),
            self::PACK->value => self::PACK->label(),
            self::LITER->value => self::LITER->label(),
            self::GRAM->value => self::GRAM->label(),
            self::KILOGRAM_GRAM->value => self::KILOGRAM_GRAM->label(),
        ];
    }

    /**
     * Get all units as key-value pairs with Arabic labels
     */
    public static function arabicOptions(): array
    {
        return [
            self::PIECE->value => self::PIECE->arabicLabel(),
            self::KILOGRAM->value => self::KILOGRAM->arabicLabel(),
            self::METER->value => self::METER->arabicLabel(),
            self::BOX->value => self::BOX->arabicLabel(),
            self::ITEM->value => self::ITEM->arabicLabel(),
            self::PACK->value => self::PACK->arabicLabel(),
            self::LITER->value => self::LITER->arabicLabel(),
            self::GRAM->value => self::GRAM->arabicLabel(),
            self::KILOGRAM_GRAM->value => self::KILOGRAM_GRAM->arabicLabel(),
        ];
    }

    /**
     * Get units grouped by category
     */
    public static function groupedOptions(): array
    {
        return [
            'Weight' => [
                self::KILOGRAM->value => self::KILOGRAM->label(),
                self::GRAM->value => self::GRAM->label(),
                self::KILOGRAM_GRAM->value => self::KILOGRAM_GRAM->label(),
            ],
            'Volume' => [
                self::LITER->value => self::LITER->label(),
            ],
            'Length' => [
                self::METER->value => self::METER->label(),
            ],
            'Count' => [
                self::PIECE->value => self::PIECE->label(),
                self::BOX->value => self::BOX->label(),
                self::ITEM->value => self::ITEM->label(),
                self::PACK->value => self::PACK->label(),
            ],
        ];
    }
}
