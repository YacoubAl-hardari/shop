<?php

namespace App\Exports;

use App\Models\MerchantCustomer;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MerchantCustomerStatementSummarySheet implements FromCollection, WithTitle, WithStyles
{
    public function __construct(
        protected MerchantCustomer $customer,
        protected ?string $merchantName = null,
        protected ?string $dateFrom = null,
        protected ?string $dateTo = null,
    ) {}

    public function collection(): Collection
    {
        $rows = collect([
            ['كشف حساب العميل'],
            [''],
            ['العميل', $this->customer->name],
            ['الهاتف', $this->customer->phone ?? '—'],
            ['البريد', $this->customer->email ?? '—'],
            ['المديونية', number_format((float) $this->customer->balance, 2).' ر.س'],
            ['الرصيد الفائض', number_format((float) $this->customer->credit_balance, 2).' ر.س'],
        ]);

        if ($this->merchantName) {
            $rows->push(['التاجر / الفرع', $this->merchantName]);
        }

        $rows->push(['']);
        $rows->push(['من تاريخ', $this->dateFrom ?? 'الكل']);
        $rows->push(['إلى تاريخ', $this->dateTo ?? 'الكل']);
        $rows->push(['تاريخ التصدير', now()->format('Y-m-d H:i:s')]);

        return $rows;
    }

    public function title(): string
    {
        return 'ملخص الحساب';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
        ];
    }
}
