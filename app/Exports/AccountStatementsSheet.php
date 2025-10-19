<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AccountStatementsSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, WithColumnWidths
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
        return $this->statements;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'التاجر',
            'المستخدم',
            'نوع المعاملة',
            'المبلغ المدين',
            'المبلغ الدائن',
            'الرصيد',
            'الوصف',
            'نوع المرجع',
            'رقم المرجع',
            'تاريخ المعاملة',
            'تاريخ الإنشاء',
        ];
    }

    /**
     * @param mixed $statement
     * @return array
     */
    public function map($statement): array
    {
        // ترجمة نوع المعاملة
        $transactionTypes = [
            'order' => 'طلب',
            'payment' => 'دفع',
            'refund' => 'استرداد',
            'adjustment' => 'تعديل',
        ];

        return [
            $statement->userMerchant->name ?? 'غير محدد',
            $statement->user->name ?? 'غير محدد',
            $transactionTypes[$statement->transaction_type] ?? $statement->transaction_type ?? '-',
            '$' . number_format($statement->debit_amount ?? 0, 2),
            '$' . number_format($statement->credit_amount ?? 0, 2),
            '$' . number_format($statement->balance ?? 0, 2),
            $statement->description ?? '-',
            $statement->reference_type ?? '-',
            $statement->reference_id ?? '-',
            $statement->transaction_date ? $statement->transaction_date->format('Y-m-d') : '-',
            $statement->created_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'كشوف حساب التجار';
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
            'A' => 25,  // التاجر
            'B' => 20,  // المستخدم
            'C' => 15,  // نوع المعاملة
            'D' => 15,  // المبلغ المدين
            'E' => 15,  // المبلغ الدائن
            'F' => 15,  // الرصيد
            'G' => 35,  // الوصف
            'H' => 20,  // نوع المرجع
            'I' => 15,  // رقم المرجع
            'J' => 15,  // تاريخ المعاملة
            'K' => 20,  // تاريخ الإنشاء
        ];
    }
}

