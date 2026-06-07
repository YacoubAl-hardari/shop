<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('sale_number');
            $table->foreignId('merchant_customer_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('total_amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('credit_amount', 12, 2)->default(0);
            $table->string('payment_type');
            $table->string('payment_method')->nullable();
            $table->string('status')->default('completed');
            $table->text('notes')->nullable();
            $table->foreignId('sold_by')->constrained('users');
            $table->timestamps();

            $table->unique(['team_id', 'sale_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_sales');
    }
};
