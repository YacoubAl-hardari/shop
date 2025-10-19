<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class UserCompleteDataExport implements WithMultipleSheets
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        // User Info Sheet
        $sheets[] = new UserInfoSheet($this->user);

        // Merchants Sheet
        $sheets[] = new UserMerchantsSheet($this->user);

        // Products Sheet (all products from all merchants)
        $sheets[] = new UserProductsSheet($this->user);

        // Orders Sheet (all orders)
        $sheets[] = new UserOrdersSheet($this->user);

        // Order Items Sheet (all order items)
        $sheets[] = new UserOrderItemsSheet($this->user);

        // Account Statements Sheet
        $sheets[] = new UserAccountStatementsSheet($this->user);

        // Account Entries Sheet
        $sheets[] = new UserAccountEntriesSheet($this->user);

        // Payment Transactions Sheet
        $sheets[] = new UserPaymentTransactionsSheet($this->user);

        // Merchant Wallets Sheet
        $sheets[] = new UserMerchantWalletsSheet($this->user);

        return $sheets;
    }
}

