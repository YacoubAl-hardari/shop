<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserAccountEntriesSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function collection()
    {
        return $this->user->accountEntries->map(function ($entry) {
            return [
                'id' => $entry->id,
                'merchant_name' => $entry->userMerchant?->name,
                'entry_number' => $entry->entry_number,
                'entry_type' => $entry->entry_type,
                'amount' => $entry->amount,
                'debit_amount' => $entry->debit_amount,
                'credit_amount' => $entry->credit_amount,
                'balance_after' => $entry->balance_after,
                'description' => $entry->description,
                'entry_date' => $entry->entry_date?->format('Y-m-d'),
                'created_at' => $entry->created_at?->format('Y-m-d H:i:s'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'المعرف',
            'التاجر',
            'رقم القيد',
            'نوع القيد',
            'المبلغ',
            'المدين',
            'الدائن',
            'الرصيد بعد',
            'الوصف',
            'تاريخ القيد',
            'تاريخ الإنشاء',
        ];
    }

    public function title(): string
    {
        return 'القيود المحاسبية';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}

