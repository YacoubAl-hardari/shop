<?php

namespace App\Exports;

use App\Models\UserMerchantAccountEntry;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StatementAccountEntriesSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, WithColumnWidths
{
    protected $statements;

    public function __construct($statements)
    {
        $this->statements = $statements;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // جمع معرفات التجار من كشوف الحساب المحددة
        $merchantIds = $this->statements->pluck('user_merchant_id')->unique()->filter();

        // إذا لم يكن هناك تجار، إرجاع مجموعة فارغة
        if ($merchantIds->isEmpty()) {
            return collect();
        }

        // جلب جميع القيود المحاسبية للتجار المحددين
        return UserMerchantAccountEntry::with(['user', 'userMerchant', 'creator'])
            ->whereIn('user_merchant_id', $merchantIds)
            ->orderBy('user_merchant_id')
            ->orderBy('entry_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
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
            'رقم المرجع',
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
            $entryTypes[$entry->entry_type] ?? $entry->entry_type ?? '-',
            '$' . number_format($entry->amount ?? 0, 2),
            '$' . number_format($entry->debit_amount ?? 0, 2),
            '$' . number_format($entry->credit_amount ?? 0, 2),
            '$' . number_format($entry->balance_after ?? 0, 2),
            $entry->description ?? '-',
            $entry->reference_type ?? '-',
            $entry->reference_id ?? '-',
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
        return 'جميع القيود المحاسبية';
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
            'K' => 15,  // رقم المرجع
            'L' => 15,  // تاريخ القيد
            'M' => 20,  // أنشأ بواسطة
            'N' => 20,  // تاريخ الإنشاء
        ];
    }
}

