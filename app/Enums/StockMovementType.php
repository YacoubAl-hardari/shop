<?php

namespace App\Enums;

enum StockMovementType: string
{
    case PURCHASE         = 'purchase';          // شراء بضاعة
    case SALE             = 'sale';              // بيع
    case SALE_RETURN      = 'sale_return';       // إرجاع من عميل
    case EXCHANGE_OUT     = 'exchange_out';      // صرف بضاعة عند الاستبدال
    case EXCHANGE_IN      = 'exchange_in';       // استلام بضاعة عند الاستبدال
    case ADJUSTMENT_ADD   = 'adjustment_add';    // تسوية يدوية — إضافة
    case ADJUSTMENT_REMOVE = 'adjustment_remove'; // تسوية يدوية — خصم
    case INVENTORY_GAIN   = 'inventory_gain';    // فائض جرد
    case INVENTORY_LOSS   = 'inventory_loss';    // عجز جرد
    case WRITE_OFF        = 'write_off';         // إتلاف / شطب
    case OPENING_BALANCE  = 'opening_balance';   // رصيد افتتاحي

    public function label(): string
    {
        return match ($this) {
            self::PURCHASE          => 'شراء بضاعة',
            self::SALE              => 'بيع',
            self::SALE_RETURN       => 'إرجاع من عميل',
            self::EXCHANGE_OUT      => 'صرف — استبدال',
            self::EXCHANGE_IN       => 'استلام — استبدال',
            self::ADJUSTMENT_ADD    => 'تسوية — إضافة',
            self::ADJUSTMENT_REMOVE => 'تسوية — خصم',
            self::INVENTORY_GAIN    => 'فائض جرد',
            self::INVENTORY_LOSS    => 'عجز جرد',
            self::WRITE_OFF         => 'إتلاف / شطب',
            self::OPENING_BALANCE   => 'رصيد افتتاحي',
        };
    }

    public function direction(): string
    {
        return match ($this) {
            self::PURCHASE, self::SALE_RETURN, self::EXCHANGE_IN,
            self::ADJUSTMENT_ADD, self::INVENTORY_GAIN, self::OPENING_BALANCE => 'in',

            self::SALE, self::EXCHANGE_OUT, self::ADJUSTMENT_REMOVE,
            self::INVENTORY_LOSS, self::WRITE_OFF => 'out',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PURCHASE, self::SALE_RETURN, self::EXCHANGE_IN,
            self::ADJUSTMENT_ADD, self::INVENTORY_GAIN, self::OPENING_BALANCE => 'success',

            self::SALE, self::EXCHANGE_OUT => 'info',

            self::ADJUSTMENT_REMOVE, self::INVENTORY_LOSS,
            self::WRITE_OFF => 'danger',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $t) => [$t->value => $t->label()])
            ->all();
    }
}
