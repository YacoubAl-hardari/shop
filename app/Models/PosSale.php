<?php

namespace App\Models;

use App\Enums\SalePaymentType;
use App\Models\Concerns\BelongsToTeam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosSale extends Model
{
    use BelongsToTeam;

    protected $fillable = [
        'team_id',
        'sale_number',
        'merchant_customer_id',
        'total_amount',
        'paid_amount',
        'credit_amount',
        'customer_credit_applied',
        'payment_type',
        'payment_method',
        'merchant_payment_account_id',
        'payment_reference',
        'status',
        'notes',
        'sold_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
        'customer_credit_applied' => 'decimal:2',
        'payment_type' => SalePaymentType::class,
    ];

    public function merchantCustomer(): BelongsTo
    {
        return $this->belongsTo(MerchantCustomer::class);
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(MerchantPaymentAccount::class, 'merchant_payment_account_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sold_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PosSaleItem::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(PosSaleReturn::class);
    }
}
