<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AccountEntriesExport implements WithMultipleSheets
{
    protected $entries;

    public function __construct($entries)
    {
        $this->entries = $entries;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        // First sheet: Account entries information
        $sheets[] = new AccountEntriesSheet($this->entries);

        // Second sheet: Account statements for merchants in the selected entries
        $sheets[] = new MerchantAccountStatementsSheet($this->entries);

        return $sheets;
    }
}

