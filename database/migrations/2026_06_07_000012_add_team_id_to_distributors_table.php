<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distributors', function (Blueprint $table) {
            if (! Schema::hasColumn('distributors', 'team_id')) {
                $table->foreignId('team_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            }
        });

        DB::table('distributors')
            ->join('suppliers', 'distributors.supplier_id', '=', 'suppliers.id')
            ->whereNull('distributors.team_id')
            ->update(['distributors.team_id' => DB::raw('suppliers.team_id')]);
    }

    public function down(): void
    {
        Schema::table('distributors', function (Blueprint $table) {
            if (Schema::hasColumn('distributors', 'team_id')) {
                $table->dropConstrainedForeignId('team_id');
            }
        });
    }
};