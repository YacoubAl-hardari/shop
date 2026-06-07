<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class JournalEntriesExport implements WithMultipleSheets
{
    public function __construct(
        protected Collection $entries,
    ) {}

    public function sheets(): array
    {
        $entries = $this->entries->load(['creator', 'lines.account']);

        return [
            new JournalEntriesSheet($entries),
            new JournalEntryLinesSheet($entries),
        ];
    }
}
