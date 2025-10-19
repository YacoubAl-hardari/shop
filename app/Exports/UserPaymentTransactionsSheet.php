<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserPaymentTransactionsSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function collection()
    {
        return $this->user->paymentTransactions->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'merchant_name' => $transaction->userMerchant?->name,
                'transaction_number' => $transaction->transaction_number,
                'amount' => $transaction->amount,
                'payment_method' => $transaction->payment_method ? ($transaction->payment_method->getLabel() ?? $transaction->payment_method->value) : null,
                'status' => $transaction->status ? ($transaction->status->getLabel() ?? $transaction->status->value) : null,
                'notes' => $transaction->notes,
                'reference_number' => $transaction->reference_number,
                'wallet_account' => $transaction->userMerchantWallet?->account_name,
                'payment_date' => $transaction->payment_date?->format('Y-m-d'),
                'created_at' => $transaction->created_at?->format('Y-m-d H:i:s'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'المعرف',
            'التاجر',
            'رقم المعاملة',
            'المبلغ',
            'طريقة الدفع',
            'الحالة',
            'ملاحظات',
            'رقم المرجع',
            'حساب المحفظة',
            'تاريخ الدفع',
            'تاريخ الإنشاء',
        ];
    }

    public function title(): string
    {
        return 'معاملات الدفع';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}

