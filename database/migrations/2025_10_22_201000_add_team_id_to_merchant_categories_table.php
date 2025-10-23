<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('merchant_categories') && !Schema::hasColumn('merchant_categories', 'team_id')) {
            Schema::table('merchant_categories', function (Blueprint $table) {
                $table->foreignId('team_id')
                    ->nullable()
                    ->constrained('teams')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('merchant_categories') && Schema::hasColumn('merchant_categories', 'team_id')) {
            Schema::table('merchant_categories', function (Blueprint $table) {
                $table->dropForeignIdFor(\App\Models\Team::class);
            });
        }
    }
};
