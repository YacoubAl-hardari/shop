<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_count_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_count_id')->constrained()->cascadeOnDelete();
            $table->foreignId('merchant_product_id')->constrained()->cascadeOnDelete();
            $table->string('product_name');
            $table->string('unit')->nullable();
            $table->decimal('book_quantity', 12, 2)->default(0);     // الكمية الدفترية
            $table->decimal('counted_quantity', 12, 2)->nullable();  // الكمية المعدودة (null = لم تُعدّ بعد)
            $table->decimal('variance_quantity', 12, 2)->default(0); // counted - book
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->decimal('book_value', 12, 2)->default(0);        // book_qty * unit_cost
            $table->decimal('counted_value', 12, 2)->default(0);     // counted_qty * unit_cost
            $table->decimal('variance_value', 12, 2)->default(0);    // variance_qty * unit_cost
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['inventory_count_id', 'merchant_product_id'], 'inv_count_items_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_count_items');
    }
};
