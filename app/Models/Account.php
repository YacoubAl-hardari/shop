<?php

namespace App\Models;

use App\Enums\AccountType;
use App\Enums\NormalBalance;
use App\Models\Concerns\BelongsToTeam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Kalnoy\Nestedset\NodeTrait;

class Account extends Model
{
    use BelongsToTeam;
    use NodeTrait;

    protected $fillable = [
        'team_id',
        'parent_id',
        'code',
        'name',
        'type',
        'normal_balance',
        'is_system',
        'is_active',
        'description',
    ];

    protected $casts = [
        'type' => AccountType::class,
        'normal_balance' => NormalBalance::class,
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function getScopeAttributes(): array
    {
        return ['team_id'];
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }
}
