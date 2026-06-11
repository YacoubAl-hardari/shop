<?php

namespace App\Models;

use App\Enums\CustomerFinancialTransferPurpose;
use App\Enums\CustomerFinancialTransferStatus;
use App\Models\Concerns\BelongsToTeam;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MerchantCustomerFinancialTransfer extends Model
{
    use BelongsToTeam;

    /**
     * Cross-tenant records: team_id is always the merchant team, set explicitly on create.
     */
    protected static function bootBelongsToTeam(): void {}

    protected $fillable = [
        'team_id',
        'merchant_customer_id',
        'statement_share_id',
        'submitted_by',
        'merchant_payment_account_id',
        'payment_method',
        'purpose',
        'amount',
        'reference_number',
        'notes',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'merchant_customer_payment_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'status' => CustomerFinancialTransferStatus::class,
        'purpose' => CustomerFinancialTransferPurpose::class,
        'reviewed_at' => 'datetime',
    ];

    public function merchantCustomer(): BelongsTo
    {
        return $this->belongsTo(MerchantCustomer::class);
    }

    public function statementShare(): BelongsTo
    {
        return $this->belongsTo(MerchantCustomerStatementShare::class, 'statement_share_id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(MerchantPaymentAccount::class, 'merchant_payment_account_id');
    }

    public function merchantCustomerPayment(): BelongsTo
    {
        return $this->belongsTo(MerchantCustomerPayment::class);
    }

    public function isPending(): bool
    {
        return $this->status === CustomerFinancialTransferStatus::PENDING;
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', CustomerFinancialTransferStatus::PENDING);
    }

    public static function acrossTeams(): Builder
    {
        return static::withoutGlobalScopes();
    }
}
