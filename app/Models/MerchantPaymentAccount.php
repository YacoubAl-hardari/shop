<?php

namespace App\Models;

use App\Enums\MerchantPaymentAccountType;
use App\Models\Concerns\BelongsToTeam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MerchantPaymentAccount extends Model
{
    use BelongsToTeam;

    protected $fillable = [
        'team_id',
        'type',
        'name',
        'account_number',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'type' => MerchantPaymentAccountType::class,
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function posSales(): HasMany
    {
        return $this->hasMany(PosSale::class);
    }

    public function customerPayments(): HasMany
    {
        return $this->hasMany(MerchantCustomerPayment::class);
    }

    public function displayLabel(): string
    {
        return "{$this->name} — {$this->account_number}";
    }
}
