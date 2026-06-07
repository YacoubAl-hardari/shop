<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MerchantCustomerStatementLinesSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(
        protected Collection $lines,
    ) {}

    public function collection(): Collection
    {
        return $this->lines;
    }

    public function headings(): array
    {
        return [
            'التاريخ',
            'رقم القيد',
            'الحساب',
            'مدين',
            'دائن',
            'الوصف',
        ];
    }

    public function map($line): array
    {
        return [
            $line->journalEntry?->entry_date?->format('Y-m-d') ?? '—',
            $line->journalEntry?->entry_number ?? '—',
            $line->account?->name ?? '—',
            number_format((float) $line->debit_amount, 2),
            number_format((float) $line->credit_amount, 2),
            $line->description ?? $line->journalEntry?->description ?? '—',
        ];
    }

    public function title(): string
    {
        return 'حركات الحساب';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 14,
            'B' => 16,
            'C' => 28,
            'D' => 14,
            'E' => 14,
            'F' => 40,
        ];
    }
}
