<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;



    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'address',
        'phone',
        'salary',
        'min_spending_limit',
        'max_spending_limit',
        'max_debt_limit',
        'debt_warning_percentage',
        'debt_danger_percentage',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'salary' => 'decimal:2',
            'min_spending_limit' => 'decimal:2',
            'max_spending_limit' => 'decimal:2',
            'max_debt_limit' => 'decimal:2',
            'debt_warning_percentage' => 'decimal:2',
            'debt_danger_percentage' => 'decimal:2',
        ];
    }

  
    /**
     * Get the user's merchants.
     */
    public function merchants()
    {
        return $this->hasMany(UserMerchant::class);
    }

    /**
     * Get the user's orders.
     */
    public function orders()
    {
        return $this->hasMany(UserMerchantOrder::class);
    }

    /**
     * Get the user's account statements.
     */
    public function accountStatements()
    {
        return $this->hasMany(UserMerchantAccountStatement::class);
    }

    /**
     * Get the user's payment transactions.
     */
    public function paymentTransactions()
    {
        return $this->hasMany(UserMerchantPaymentTransaction::class);
    }

    /**
     * Get the user's account entries.
     */
    public function accountEntries()
    {
        return $this->hasMany(UserMerchantAccountEntry::class);
    }

    /**
     * Get the user's budgets.
     */
    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    /**
     * Get the user's budget categories.
     */
    public function budgetCategories()
    {
        return $this->hasMany(BudgetCategory::class);
    }

    /**
     * Get the user's budget alerts.
     */
    public function budgetAlerts()
    {
        return $this->hasMany(BudgetAlert::class);
    }

    /**
     * Get the user's active budget.
     */
    public function activeBudget()
    {
        return $this->hasOne(Budget::class)->where('is_active', true)->latest();
    }

    /**
     * Get total debt across all merchants.
     */
    public function getTotalDebt(): float
    {
        return $this->merchants()->sum('balance');
    }

    /**
     * Get the user's teams.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the teams that the user belongs to.
     * Each user can only belong to ONE team.
     */
    public function getTenants(Panel $panel): Collection
    {
        return $this->teams()->limit(1)->get();
    }

    /**
     * Check if the user can access the tenant.
     */
    public function canAccessTenant(Model $tenant): bool
    {
        return $this->teams()->whereKey($tenant)->exists();
    }

    /**
     * Check if the user can access the panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
