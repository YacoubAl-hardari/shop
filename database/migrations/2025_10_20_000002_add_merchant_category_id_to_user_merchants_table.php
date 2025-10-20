<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_merchants', function (Blueprint $table) {
            if (!Schema::hasColumn('user_merchants', 'merchant_category_id')) {
                $table->foreignId('merchant_category_id')->nullable()->after('balance')->constrained('merchant_categories')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_merchants', function (Blueprint $table) {
            if (Schema::hasColumn('user_merchants', 'merchant_category_id')) {
                $table->dropConstrainedForeignId('merchant_category_id');
            }
        });
    }
};


