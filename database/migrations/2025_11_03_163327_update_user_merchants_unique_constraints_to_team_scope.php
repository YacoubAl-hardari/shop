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
        Schema::table('user_merchants', function (Blueprint $table) {
            // Drop the old global unique constraints
            $table->dropUnique('user_merchants_email_unique');
            $table->dropUnique('user_merchants_phone_unique');
            
            // Add composite unique constraints scoped to team_id
            // This allows the same email/phone to exist for different teams
            $table->unique(['email', 'team_id'], 'user_merchants_email_team_unique');
            $table->unique(['phone', 'team_id'], 'user_merchants_phone_team_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_merchants', function (Blueprint $table) {
            // Drop the team-scoped unique constraints
            $table->dropUnique('user_merchants_email_team_unique');
            $table->dropUnique('user_merchants_phone_team_unique');
            
            // Restore the global unique constraints
            $table->unique('email', 'user_merchants_email_unique');
            $table->unique('phone', 'user_merchants_phone_unique');
        });
    }
};
