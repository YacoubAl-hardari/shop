<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserOrdersSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function collection()
    {
        return $this->user->orders->map(function ($order) {
            return [
                'id' => $order->id,
                'merchant_name' => $order->userMerchant?->name,
                'order_number' => $order->order_number,
                'note' => $order->note,
                'total_price' => $order->total_price,
                'items_count' => $order->orderItems->count(),
                'created_at' => $order->created_at?->format('Y-m-d H:i:s'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'المعرف',
            'التاجر',
            'رقم الطلب',
            'ملاحظات',
            'المجموع',
            'عدد العناصر',
            'تاريخ الإنشاء',
        ];
    }

    public function title(): string
    {
        return 'الطلبات';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}

