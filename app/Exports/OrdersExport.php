<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class OrdersExport implements WithMultipleSheets
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        // First sheet: Main orders information
        $sheets[] = new OrdersMainSheet($this->orders);

        // Second sheet: All order items combined in one sheet
        $sheets[] = new AllOrderItemsSheet($this->orders);

        return $sheets;
    }
}

