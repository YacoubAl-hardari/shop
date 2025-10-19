<?php

use App\Enums\ProductUnit;
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
        Schema::create('user_merchant_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_merchant_order_id')->constrained('user_merchant_orders');
            $table->foreignId('user_merchant_product_id')->constrained('user_merchant_products');
            $table->enum('unit', ProductUnit::values());
            $table->decimal('quantity', 10, 2);
            $table->decimal('price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_merchant_order_items');
    }
};
