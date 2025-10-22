<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTeam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MerchantCategory extends Model
{
    use BelongsToTeam;

    protected $fillable = [
        'team_id',
        'name',
        'icon',
        'color',
    ];

    public function merchants(): HasMany
    {
        return $this->hasMany(UserMerchant::class);
    }
}

