<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserMerchantWalletsSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function collection()
    {
        $wallets = collect();
        
        foreach ($this->user->merchants as $merchant) {
            foreach ($merchant->wallets as $wallet) {
                $wallets->push([
                    'id' => $wallet->id,
                    'merchant_name' => $merchant->name,
                    'account_name' => $wallet->account_name,
                    'bank_account_number' => $wallet->bank_account_number,
                    'bank_name' => $wallet->bank_name,
                    'is_active' => $wallet->is_active ? 'نعم' : 'لا',
                    'created_at' => $wallet->created_at?->format('Y-m-d H:i:s'),
                ]);
            }
        }
        
        return $wallets;
    }

    public function headings(): array
    {
        return [
            'المعرف',
            'التاجر',
            'اسم الحساب',
            'رقم الحساب البنكي',
            'اسم البنك',
            'نشط',
            'تاريخ الإنشاء',
        ];
    }

    public function title(): string
    {
        return 'المحافظ البنكية';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}

