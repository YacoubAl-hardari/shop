<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserOrderItemsSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function collection()
    {
        $items = collect();
        
        foreach ($this->user->orders as $order) {
            foreach ($order->orderItems as $item) {
                $items->push([
                    'id' => $item->id,
                    'order_number' => $order->order_number,
                    'merchant_name' => $order->userMerchant?->name,
                    'product_name' => $item->product?->name,
                    'unit' => $item->unit,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total_price' => $item->total_price,
                    'created_at' => $item->created_at?->format('Y-m-d H:i:s'),
                ]);
            }
        }
        
        return $items;
    }

    public function headings(): array
    {
        return [
            'المعرف',
            'رقم الطلب',
            'التاجر',
            'المنتج',
            'الوحدة',
            'الكمية',
            'السعر',
            'المجموع',
            'تاريخ الإنشاء',
        ];
    }

    public function title(): string
    {
        return 'عناصر الطلبات';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}

