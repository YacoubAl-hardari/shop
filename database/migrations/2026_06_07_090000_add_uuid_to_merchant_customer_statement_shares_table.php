<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merchant_customer_statement_shares', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique()->after('id');
        });

        DB::table('merchant_customer_statement_shares')
            ->whereNull('uuid')
            ->orderBy('id')
            ->lazyById()
            ->each(function (object $share): void {
                DB::table('merchant_customer_statement_shares')
                    ->where('id', $share->id)
                    ->update(['uuid' => (string) Str::uuid()]);
            });

        Schema::table('merchant_customer_statement_shares', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('merchant_customer_statement_shares', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
