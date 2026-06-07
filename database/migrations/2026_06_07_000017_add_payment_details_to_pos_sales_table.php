<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_sales', function (Blueprint $table) {
            $table->foreignId('merchant_payment_account_id')
                ->nullable()
                ->after('payment_method')
                ->constrained()
                ->nullOnDelete();
            $table->string('payment_reference')->nullable()->after('merchant_payment_account_id');
        });
    }

    public function down(): void
    {
        Schema::table('pos_sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('merchant_payment_account_id');
            $table->dropColumn('payment_reference');
        });
    }
};
