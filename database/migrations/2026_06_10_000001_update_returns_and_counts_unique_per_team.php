<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_sale_returns', function (Blueprint $table) {
            $table->dropUnique('pos_sale_returns_return_number_unique');
            $table->unique(['team_id', 'return_number']);
        });

        Schema::table('inventory_counts', function (Blueprint $table) {
            $table->dropUnique('inventory_counts_count_number_unique');
            $table->unique(['team_id', 'count_number']);
        });
    }

    public function down(): void
    {
        Schema::table('pos_sale_returns', function (Blueprint $table) {
            $table->dropUnique(['team_id', 'return_number']);
            $table->unique('return_number');
        });

        Schema::table('inventory_counts', function (Blueprint $table) {
            $table->dropUnique(['team_id', 'count_number']);
            $table->unique('count_number');
        });
    }
};
