<?php

namespace App\Exports;

use App\Models\Team;
use App\Services\TeamDataExportService;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MerchantFinancialDataExport implements WithMultipleSheets
{
    public function __construct(
        protected Team $team,
    ) {}

    public function sheets(): array
    {
        $definitions = app(TeamDataExportService::class)->toFinancialExcelSheets($this->team);

        return collect($definitions)->map(fn (array $sheet) => new ArraySheet(
            $sheet['rows'],
            $sheet['headings'],
            $sheet['title'],
        ))->all();
    }
}
