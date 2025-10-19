<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserMerchantsSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function collection()
    {
        return $this->user->merchants->map(function ($merchant) {
            return [
                'id' => $merchant->id,
                'name' => $merchant->name,
                'email' => $merchant->email,
                'phone' => $merchant->phone,
                'information' => $merchant->information,
                'is_active' => $merchant->is_active ? 'نعم' : 'لا',
                'balance' => $merchant->balance,
                'created_at' => $merchant->created_at?->format('Y-m-d H:i:s'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'المعرف',
            'الاسم',
            'البريد الإلكتروني',
            'الهاتف',
            'المعلومات',
            'نشط',
            'الرصيد',
            'تاريخ الإنشاء',
        ];
    }

    public function title(): string
    {
        return 'التجار';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}

