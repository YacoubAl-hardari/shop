<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_merchant_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_merchant_id')->constrained('user_merchants');
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->string('barcode')->nullable();
            $table->string('description')->nullable();
            $table->string('image')->nullable();
            $table->string('brand')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_merchant_products');
    }
};
