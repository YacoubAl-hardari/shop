<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserInfoSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function collection()
    {
        return collect([
            [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'role' => $this->user->role,
                'phone' => $this->user->phone,
                'address' => $this->user->address,
                'created_at' => $this->user->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $this->user->updated_at?->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'المعرف',
            'الاسم',
            'البريد الإلكتروني',
            'الدور',
            'الهاتف',
            'العنوان',
            'تاريخ الإنشاء',
            'تاريخ التحديث',
        ];
    }

    public function title(): string
    {
        return 'معلومات المستخدم';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}

