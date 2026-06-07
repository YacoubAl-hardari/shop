<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = collect(DB::select('SHOW INDEX FROM user_merchant_account_entries'))
            ->pluck('Key_name')
            ->unique()
            ->map(fn ($name) => strtolower($name));

        Schema::table('user_merchant_account_entries', function (Blueprint $table) use ($indexes) {
            if ($indexes->contains('user_merchant_account_entries_entry_number_unique')) {
                $table->dropUnique('user_merchant_account_entries_entry_number_unique');
            }

            if ($indexes->contains('entry_number') && ! $indexes->contains('umae_team_entry_number_unique')) {
                $table->dropUnique(['entry_number']);
            }

            if (! $indexes->contains('umae_team_entry_number_unique')) {
                $table->unique(['team_id', 'entry_number'], 'umae_team_entry_number_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_merchant_account_entries', function (Blueprint $table) {
            $table->dropUnique('umae_team_entry_number_unique');
            $table->unique('entry_number');
        });
    }
};