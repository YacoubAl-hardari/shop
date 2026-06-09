<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_sale_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_sale_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pos_sale_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('merchant_product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->decimal('quantity_returned', 12, 2);
            $table->decimal('unit_price', 12, 2);        // سعر البيع الأصلي
            $table->decimal('total_price', 12, 2);       // quantity_returned * unit_price
            $table->decimal('unit_cost', 12, 2)->default(0);  // تكلفة الشراء وقت الإرجاع
            $table->string('return_reason')->nullable();  // defective|changed_mind|wrong_item|other
            $table->string('item_condition')->nullable(); // resellable|damaged|disposed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_sale_return_items');
    }
};
