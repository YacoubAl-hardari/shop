<?php

namespace App\Enums;

enum WalletTransactionStatus: string
{
    case DEPOSIT = 'إيداع';
    case WITHDRAWAL = 'سحب';
    case TRANSFER = 'تحويل';
    case RECEIVED = 'استقبال';
    case SENT = 'إرسال';
    case REFUND = 'مرتجع';
    case PURCHASE = 'مشتريات';
    case SALE = 'بيع';
    case PURCHASE_REFUND = 'مرتجع مشتريات';
    case SALE_REFUND = 'مرتجع بيع';

    /**
     * Get all enum values as an array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get the default status
     */
    public static function default(): self
    {
        return self::DEPOSIT;
    }

    /**
     * Get the label for the status
     */
    public function label(): string
    {
        return match($this) {
            self::DEPOSIT => 'إيداع',
            self::WITHDRAWAL => 'سحب',
            self::TRANSFER => 'تحويل',
            self::RECEIVED => 'استقبال',
            self::SENT => 'إرسال',
            self::REFUND => 'مرتجع',
            self::PURCHASE => 'مشتريات',
            self::SALE => 'بيع',
            self::PURCHASE_REFUND => 'مرتجع مشتريات',
            self::SALE_REFUND => 'مرتجع بيع',
        };
    }

    /**
     * Check if the status is a credit transaction
     */
    public function isCredit(): bool
    {
        return in_array($this, [
            self::DEPOSIT,
            self::RECEIVED,
            self::SALE,
            self::PURCHASE_REFUND,
        ]);
    }

    /**
     * Check if the status is a debit transaction
     */
    public function isDebit(): bool
    {
        return in_array($this, [
            self::WITHDRAWAL,
            self::SENT,
            self::PURCHASE,
            self::SALE_REFUND,
        ]);
    }

    /**
     * Check if the status is a transfer transaction
     */
    public function isTransfer(): bool
    {
        return in_array($this, [
            self::TRANSFER,
            self::SENT,
            self::RECEIVED,
        ]);
    }

    /**
     * Check if the status is a refund transaction
     */
    public function isRefund(): bool
    {
        return in_array($this, [
            self::REFUND,
            self::PURCHASE_REFUND,
            self::SALE_REFUND,
        ]);
    }
}
