<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_sale_returns', function (Blueprint $table) {
            $table->decimal('receivable_reduction_amount', 12, 2)
                ->default(0)
                ->after('refunded_to_customer');
        });
    }

    public function down(): void
    {
        Schema::table('pos_sale_returns', function (Blueprint $table) {
            $table->dropColumn('receivable_reduction_amount');
        });
    }
};
