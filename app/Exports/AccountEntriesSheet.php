<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AccountEntriesSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, WithColumnWidths
{
    protected $entries;

    public function __construct($entries)
    {
        $this->entries = $entries;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->entries;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'رقم القيد',
            'التاجر',
            'المستخدم',
            'نوع القيد',
            'المبلغ',
            'المبلغ المدين',
            'المبلغ الدائن',
            'الرصيد بعد المعاملة',
            'الوصف',
            'نوع المرجع',
            'تاريخ القيد',
            'أنشأ بواسطة',
            'تاريخ الإنشاء',
        ];
    }

    /**
     * @param mixed $entry
     * @return array
     */
    public function map($entry): array
    {
        // ترجمة نوع القيد
        $entryTypes = [
            'debit' => 'مدين',
            'credit' => 'دائن',
            'adjustment' => 'تعديل',
        ];

        return [
            $entry->entry_number,
            $entry->userMerchant->name ?? 'غير محدد',
            $entry->user->name ?? 'غير محدد',
            $entryTypes[$entry->entry_type] ?? $entry->entry_type,
            '$' . number_format($entry->amount ?? 0, 2),
            '$' . number_format($entry->debit_amount ?? 0, 2),
            '$' . number_format($entry->credit_amount ?? 0, 2),
            '$' . number_format($entry->balance_after ?? 0, 2),
            $entry->description ?? '-',
            $entry->reference_type ?? '-',
            $entry->entry_date ? $entry->entry_date->format('Y-m-d') : '-',
            $entry->creator->name ?? 'غير محدد',
            $entry->created_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'القيود المحاسبية';
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15,  // رقم القيد
            'B' => 25,  // التاجر
            'C' => 20,  // المستخدم
            'D' => 12,  // نوع القيد
            'E' => 12,  // المبلغ
            'F' => 15,  // المبلغ المدين
            'G' => 15,  // المبلغ الدائن
            'H' => 20,  // الرصيد بعد المعاملة
            'I' => 35,  // الوصف
            'J' => 20,  // نوع المرجع
            'K' => 15,  // تاريخ القيد
            'L' => 20,  // أنشأ بواسطة
            'M' => 20,  // تاريخ الإنشاء
        ];
    }
}

