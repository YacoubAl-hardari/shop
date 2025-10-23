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
            if (!$model->team_id) {
                try {
                    $tenant = Filament::getTenant();
                    if ($tenant instanceof Team) {
                        $model->team_id = $tenant->id;
                    }
                } catch (\Exception $e) {
                    // Tenant not available in this context
                }
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

