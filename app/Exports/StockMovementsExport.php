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

class StockMovementsExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(
        protected Collection $records,
    ) {}

    public function collection(): Collection
    {
        return $this->records;
    }

    public function headings(): array
    {
        return [
            'التاريخ والوقت',
            'المنتج',
            'الباركود',
            'نوع الحركة',
            'الاتجاه',
            'الكمية',
            'الرصيد قبل',
            'الرصيد بعد',
            'تكلفة الوحدة',
            'إجمالي التكلفة',
            'ملاحظات',
        ];
    }

    public function map($row): array
    {
        return [
            $row->created_at ? $row->created_at->timezone('Asia/Riyadh')->format('Y/m/d H:i') : '—',
            $row->product?->name ?? '—',
            $row->product?->barcode ? ' ' . $row->product->barcode : '—', // Added space to prevent Excel from converting long barcodes to scientific notation
            $row->movement_type?->label() ?? '—',
            $row->direction === 'in' ? 'داخل' : 'خارج',
            $row->quantity,
            $row->quantity_before,
            $row->quantity_after,
            $row->unit_cost,
            $row->total_cost,
            $row->notes ?? '—',
        ];
    }

    public function title(): string
    {
        return 'حركات المخزون';
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
            'A' => 20, // التاريخ والوقت
            'B' => 30, // المنتج
            'C' => 18, // الباركود
            'D' => 18, // نوع الحركة
            'E' => 12, // الاتجاه
            'F' => 12, // الكمية
            'G' => 14, // الرصيد قبل
            'H' => 14, // الرصيد بعد
            'I' => 14, // تكلفة الوحدة
            'J' => 16, // إجمالي التكلفة
            'K' => 30, // ملاحظات
        ];
    }
}
