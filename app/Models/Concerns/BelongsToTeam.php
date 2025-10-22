<?php

namespace App\Models\Concerns;

use App\Models\Team;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTeam
{
    /**
     * Boot the trait.
     */
    protected static function bootBelongsToTeam(): void
    {
        static::creating(function ($model) {
            if (Filament::hasTenant() && !$model->team_id) {
                $model->team_id = Filament::getTenant()->id;
            }
        });
    }

    /**
     * Get the team that owns the model.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}

