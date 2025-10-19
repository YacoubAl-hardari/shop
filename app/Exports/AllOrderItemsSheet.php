<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AllOrderItemsSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, WithColumnWidths
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
        // جمع جميع عناصر الطلبات من جميع الطلبات
        $allItems = collect();
        
        foreach ($this->orders as $order) {
            foreach ($order->orderItems as $item) {
                // إضافة معلومات الطلب لكل عنصر
                $item->order_data = $order;
                $allItems->push($item);
            }
        }
        
        return $allItems;
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
            'المنتج',
            'الوحدة',
            'الكمية',
            'السعر',
            'إجمالي السعر',
            'تاريخ الطلب',
        ];
    }

    /**
     * @param mixed $item
     * @return array
     */
    public function map($item): array
    {
        return [
            $item->order_data->order_number,
            $item->order_data->userMerchant->name ?? 'غير محدد',
            $item->order_data->user->name ?? 'غير محدد',
            $item->product->name ?? 'منتج محذوف',
            $item->unit ?? '-',
            number_format($item->quantity, 2),
            '$' . number_format($item->price, 2),
            '$' . number_format($item->total_price, 2),
            $item->order_data->created_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'جميع عناصر الطلبات';
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
            'D' => 30,  // المنتج
            'E' => 12,  // الوحدة
            'F' => 10,  // الكمية
            'G' => 12,  // السعر
            'H' => 15,  // إجمالي السعر
            'I' => 20,  // تاريخ الطلب
        ];
    }
}

