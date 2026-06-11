<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class MerchantCustomerStatementShare extends Model
{
    protected $fillable = [
        'uuid',
        'team_id',
        'merchant_customer_id',
        'user_id',
        'shared_by',
        'closed_by',
        'is_active',
        'shared_at',
        'closed_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'shared_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function merchantCustomer(): BelongsTo
    {
        return $this->belongsTo(MerchantCustomer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sharedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_by');
    }

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function isOpen(): bool
    {
        return $this->is_active;
    }

    public function financialTransfers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MerchantCustomerFinancialTransfer::class, 'statement_share_id');
    }

    protected static function booted(): void
    {
        static::creating(function (self $share): void {
            if (blank($share->uuid)) {
                $share->uuid = (string) Str::uuid();
            }
        });
    }
}
