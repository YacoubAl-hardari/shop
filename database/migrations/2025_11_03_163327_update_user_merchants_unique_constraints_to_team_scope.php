<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = collect(DB::select('SHOW INDEX FROM user_merchants'))
            ->pluck('Key_name')
            ->unique()
            ->map(fn ($name) => strtolower($name));

        Schema::table('user_merchants', function (Blueprint $table) use ($indexes) {
            if ($indexes->contains('user_merchants_email_unique')) {
                $table->dropUnique('user_merchants_email_unique');
            }

            if ($indexes->contains('user_merchants_phone_unique')) {
                $table->dropUnique('user_merchants_phone_unique');
            }

            if (! $indexes->contains('user_merchants_email_team_unique')) {
                $table->unique(['email', 'team_id'], 'user_merchants_email_team_unique');
            }

            if (! $indexes->contains('user_merchants_phone_team_unique')) {
                $table->unique(['phone', 'team_id'], 'user_merchants_phone_team_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_merchants', function (Blueprint $table) {
            $table->dropUnique('user_merchants_email_team_unique');
            $table->dropUnique('user_merchants_phone_team_unique');
            $table->unique('email', 'user_merchants_email_unique');
            $table->unique('phone', 'user_merchants_phone_unique');
        });
    }
};