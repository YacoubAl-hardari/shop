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

class JournalEntryLinesSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(
        protected Collection $entries,
    ) {}

    public function collection(): Collection
    {
        return $this->entries->flatMap(function ($entry) {
            return $entry->lines->map(fn ($line) => (object) [
                'entry_number' => $entry->entry_number,
                'account_code' => $line->account?->code,
                'account_name' => $line->account?->name,
                'debit_amount' => $line->debit_amount,
                'credit_amount' => $line->credit_amount,
                'description' => $line->description,
                'subledger_label' => $this->subledgerLabel($line),
            ]);
        });
    }

    protected function subledgerLabel($line): string
    {
        if (! $line->subledger_type || ! $line->subledger_id) {
            return '—';
        }

        return class_basename($line->subledger_type).' #'.$line->subledger_id;
    }

    public function headings(): array
    {
        return [
            'رقم القيد',
            'رمز الحساب',
            'اسم الحساب',
            'مدين',
            'دائن',
            'الوصف',
            'العميل الفرعي',
        ];
    }

    public function map($line): array
    {
        return [
            $line->entry_number,
            $line->account_code ?? '—',
            $line->account_name ?? '—',
            number_format((float) $line->debit_amount, 2),
            number_format((float) $line->credit_amount, 2),
            $line->description ?? '—',
            $line->subledger_label,
        ];
    }

    public function title(): string
    {
        return 'بنود القيود';
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
            'A' => 16,
            'B' => 14,
            'C' => 28,
            'D' => 14,
            'E' => 14,
            'F' => 35,
            'G' => 20,
        ];
    }
}
