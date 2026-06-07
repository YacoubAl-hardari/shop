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

class JournalEntriesSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(
        protected Collection $entries,
    ) {}

    public function collection(): Collection
    {
        return $this->entries;
    }

    public function headings(): array
    {
        return [
            'رقم القيد',
            'التاريخ',
            'الوصف',
            'الحالة',
            'المرجع',
            'تاريخ الترحيل',
            'أنشئ بواسطة',
        ];
    }

    public function map($entry): array
    {
        return [
            $entry->entry_number,
            $entry->entry_date?->format('Y-m-d') ?? '—',
            $entry->description ?? '—',
            $entry->status?->arabicLabel() ?? '—',
            $entry->reference_type ? class_basename($entry->reference_type).' #'.$entry->reference_id : '—',
            $entry->posted_at?->format('Y-m-d H:i:s') ?? '—',
            $entry->creator?->name ?? '—',
        ];
    }

    public function title(): string
    {
        return 'القيود اليومية';
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
            'C' => 40,
            'D' => 14,
            'E' => 24,
            'F' => 20,
            'G' => 20,
        ];
    }
}
