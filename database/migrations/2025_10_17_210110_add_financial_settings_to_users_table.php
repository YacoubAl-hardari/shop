<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Monthly salary for financial monitoring
            $table->decimal('salary', 10, 2)->nullable()->after('phone');
            
            // Minimum and maximum spending/debt limits
            $table->decimal('min_spending_limit', 10, 2)->nullable()->after('salary');
            $table->decimal('max_spending_limit', 10, 2)->nullable()->after('min_spending_limit');
            $table->decimal('max_debt_limit', 10, 2)->nullable()->after('max_spending_limit');
            
            // Alert thresholds (percentage of salary)
            $table->decimal('debt_warning_percentage', 5, 2)->default(50)->after('max_debt_limit');
            $table->decimal('debt_danger_percentage', 5, 2)->default(80)->after('debt_warning_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'salary',
                'min_spending_limit',
                'max_spending_limit',
                'max_debt_limit',
                'debt_warning_percentage',
                'debt_danger_percentage',
            ]);
        });
    }
};
