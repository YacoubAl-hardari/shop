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
        Schema::create('user_merchant_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_merchant_id')->constrained('user_merchants');
            $table->foreignId('user_id')->constrained('users');
            $table->string('order_number');
            $table->string('note')->nullable();
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_merchant_orders');
    }
};
