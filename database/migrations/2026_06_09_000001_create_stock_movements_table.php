<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('merchant_product_id')->constrained()->cascadeOnDelete();
            $table->string('movement_type'); // StockMovementType enum
            $table->string('direction');     // in | out
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->decimal('quantity_before', 12, 2)->default(0);
            $table->decimal('quantity_after', 12, 2)->default(0);
            $table->nullableMorphs('reference');   // PosSale, PosSaleReturn, InventoryCount, etc.
            $table->foreignId('journal_entry_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['team_id', 'merchant_product_id', 'created_at']);
            $table->index(['team_id', 'movement_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
