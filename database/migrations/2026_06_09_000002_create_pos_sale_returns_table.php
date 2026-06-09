<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_sale_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pos_sale_id')->constrained()->cascadeOnDelete();
            $table->string('return_number')->unique();
            $table->string('return_type');        // return | exchange
            $table->string('refund_method')->nullable(); // cash | credit_note | none (exchange only)
            $table->decimal('returned_amount', 12, 2)->default(0);  // إجمالي قيمة الأصناف المُرجَعة
            $table->decimal('exchange_amount', 12, 2)->default(0);  // إجمالي قيمة أصناف الاستبدال
            $table->decimal('price_difference', 12, 2)->default(0); // exchange_amount - returned_amount (موجب = عميل يدفع / سالب = نسترد له)
            $table->decimal('refunded_to_customer', 12, 2)->default(0); // نقد مُستردّ للعميل
            $table->decimal('charged_to_customer', 12, 2)->default(0);  // مبلغ إضافي على العميل
            $table->decimal('credit_note_amount', 12, 2)->default(0);   // رصيد دائن أُضيف للعميل
            $table->string('status')->default('completed');  // completed | voided
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['team_id', 'pos_sale_id']);
            $table->index(['team_id', 'return_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_sale_returns');
    }
};
