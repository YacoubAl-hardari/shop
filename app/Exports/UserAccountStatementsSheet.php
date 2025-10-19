<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserAccountStatementsSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function collection()
    {
        return $this->user->accountStatements->map(function ($statement) {
            return [
                'id' => $statement->id,
                'merchant_name' => $statement->userMerchant?->name,
                'debit_amount' => $statement->debit_amount,
                'credit_amount' => $statement->credit_amount,
                'balance' => $statement->balance,
                'transaction_type' => $statement->transaction_type,
                'description' => $statement->description,
                'transaction_date' => $statement->transaction_date?->format('Y-m-d'),
                'created_at' => $statement->created_at?->format('Y-m-d H:i:s'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'المعرف',
            'التاجر',
            'المدين',
            'الدائن',
            'الرصيد',
            'نوع المعاملة',
            'الوصف',
            'تاريخ المعاملة',
            'تاريخ الإنشاء',
        ];
    }

    public function title(): string
    {
        return 'كشوف الحسابات';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}

