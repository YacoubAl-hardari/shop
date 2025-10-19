<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrdersMainSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, WithColumnWidths
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->orders;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'رقم الطلب',
            'التاجر',
            'المستخدم',
            'إجمالي السعر',
            'عدد المنتجات',
            'الملاحظات',
            'تاريخ الإنشاء',
        ];
    }

    /**
     * @param mixed $order
     * @return array
     */
    public function map($order): array
    {
        return [
            $order->order_number,
            $order->userMerchant->name ?? 'غير محدد',
            $order->user->name ?? 'غير محدد',
            '$' . number_format($order->total_price, 2),
            $order->orderItems->count(),
            $order->note ?? '-',
            $order->created_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'الطلبات الرئيسية';
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
            'A' => 15,  // رقم الطلب
            'B' => 25,  // التاجر
            'C' => 20,  // المستخدم
            'D' => 30,  // الملاحظات
            'E' => 15,  // إجمالي السعر
            'F' => 12,  // عدد المنتجات
            'G' => 20,  // تاريخ الإنشاء
            'H' => 20,  // تاريخ التحديث
        ];
    }
}

