<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTeam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MerchantCustomerPayment extends Model
{
    use BelongsToTeam;

    protected $fillable = [
        'team_id',
        'merchant_customer_id',
        'merchant_payment_account_id',
        'payment_method',
        'amount',
        'applied_to_balance',
        'surplus_to_credit',
        'reference_number',
        'notes',
        'received_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'applied_to_balance' => 'decimal:2',
        'surplus_to_credit' => 'decimal:2',
    ];

    public function merchantCustomer(): BelongsTo
    {
        return $this->belongsTo(MerchantCustomer::class);
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(MerchantPaymentAccount::class, 'merchant_payment_account_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
