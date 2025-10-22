<?php

namespace App\Models;

use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasCurrentTenantLabel;
use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Team extends Model implements HasAvatar, HasCurrentTenantLabel, HasName
{
    protected $fillable = [
        'name',
        'slug',
        'domain',
        'description',
        'avatar_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($team) {
            if (empty($team->slug)) {
                $team->slug = Str::slug($team->name);
            }
        });
    }

    /**
     * Get the team's members.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the team's merchants.
     */
    public function merchants(): HasMany
    {
        return $this->hasMany(UserMerchant::class);
    }

    /**
     * Get the team's orders.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(UserMerchantOrder::class);
    }

    /**
     * Get the team's products.
     */
    public function products(): HasMany
    {
        return $this->hasMany(UserMerchantProduct::class);
    }

    /**
     * Get the team's account statements.
     */
    public function accountStatements(): HasMany
    {
        return $this->hasMany(UserMerchantAccountStatement::class);
    }

    /**
     * Get the team's payment transactions.
     */
    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(UserMerchantPaymentTransaction::class);
    }

    /**
     * Get the team's account entries.
     */
    public function accountEntries(): HasMany
    {
        return $this->hasMany(UserMerchantAccountEntry::class);
    }

    /**
     * Get the team's wallets.
     */
    public function wallets(): HasMany
    {
        return $this->hasMany(UserMerchantWallet::class);
    }

    /**
     * Get the team's budgets.
     */
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    /**
     * Get the team's budget categories.
     */
    public function budgetCategories(): HasMany
    {
        return $this->hasMany(BudgetCategory::class);
    }

    /**
     * Get the team's budget alerts.
     */
    public function budgetAlerts(): HasMany
    {
        return $this->hasMany(BudgetAlert::class);
    }

    /**
     * Get the Filament avatar URL for the team.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url;
    }

    /**
     * Get the Filament name for the team.
     */
    public function getFilamentName(): string
    {
        return $this->name;
    }

    /**
     * Get the current tenant label.
     */
    public function getCurrentTenantLabel(): string
    {
        return 'فريق نشط'; // Active team in Arabic
    }
}
