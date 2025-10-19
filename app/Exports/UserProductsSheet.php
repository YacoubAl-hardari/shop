<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserProductsSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function collection()
    {
        $products = collect();
        
        foreach ($this->user->merchants as $merchant) {
            foreach ($merchant->products as $product) {
                $products->push([
                    'id' => $product->id,
                    'merchant_name' => $merchant->name,
                    'name' => $product->name,
                    'price' => $product->price,
                    'barcode' => $product->barcode,
                    'brand' => $product->brand,
                    'description' => $product->description,
                    'is_active' => $product->is_active ? 'نعم' : 'لا',
                    'created_at' => $product->created_at?->format('Y-m-d H:i:s'),
                ]);
            }
        }
        
        return $products;
    }

    public function headings(): array
    {
        return [
            'المعرف',
            'التاجر',
            'اسم المنتج',
            'السعر',
            'الباركود',
            'العلامة التجارية',
            'الوصف',
            'نشط',
            'تاريخ الإنشاء',
        ];
    }

    public function title(): string
    {
        return 'المنتجات';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}

