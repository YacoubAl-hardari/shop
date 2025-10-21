<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MerchantCategory extends Model
{
    protected $fillable = [
        'name',
        'icon',
        'color',
    ];

    public function merchants(): HasMany
    {
        return $this->hasMany(UserMerchant::class);
    }
}

