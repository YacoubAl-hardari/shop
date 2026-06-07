<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTeam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use BelongsToTeam;

    protected $fillable = [
        'team_id',
        'name',
        'phone',
        'email',
        'tax_number',
        'balance',
        'is_active',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function distributors(): HasMany
    {
        return $this->hasMany(Distributor::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(MerchantProduct::class);
    }
}
