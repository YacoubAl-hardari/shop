<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTeam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MerchantProduct extends Model
{
    use BelongsToTeam;

    protected $fillable = [
        'team_id',
        'supplier_id',
        'distributor_id',
        'name',
        'sku',
        'barcode',
        'price',
        'cost',
        'stock_quantity',
        'unit',
        'is_active',
        'description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'stock_quantity' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }

    public function posSaleItems(): HasMany
    {
        return $this->hasMany(PosSaleItem::class);
    }
}
