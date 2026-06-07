<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merchant_products', function (Blueprint $table) {
            $table->string('barcode')->nullable()->after('sku');
            $table->foreignId('supplier_id')->nullable()->after('team_id')->constrained()->nullOnDelete();
            $table->foreignId('distributor_id')->nullable()->after('supplier_id')->constrained()->nullOnDelete();

            $table->index(['team_id', 'barcode']);
        });
    }

    public function down(): void
    {
        Schema::table('merchant_products', function (Blueprint $table) {
            $table->dropIndex(['team_id', 'barcode']);
            $table->dropConstrainedForeignId('distributor_id');
            $table->dropConstrainedForeignId('supplier_id');
            $table->dropColumn('barcode');
        });
    }
};
