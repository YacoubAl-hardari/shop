<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AccountStatementsExport implements WithMultipleSheets
{
    protected $statements;

    public function __construct($statements)
    {
        $this->statements = $statements;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        // First sheet: Account statements
        $sheets[] = new AccountStatementsSheet($this->statements);

        // Second sheet: All account entries for the merchants in selected statements
        $sheets[] = new StatementAccountEntriesSheet($this->statements);

        return $sheets;
    }
}

