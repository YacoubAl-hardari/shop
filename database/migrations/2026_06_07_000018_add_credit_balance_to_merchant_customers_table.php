<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merchant_customers', function (Blueprint $table) {
            $table->decimal('credit_balance', 12, 2)->default(0)->after('balance');
        });
    }

    public function down(): void
    {
        Schema::table('merchant_customers', function (Blueprint $table) {
            $table->dropColumn('credit_balance');
        });
    }
};
