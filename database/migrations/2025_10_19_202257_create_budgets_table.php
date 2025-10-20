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
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name'); // اسم الميزانية (مثل: "ميزانية شهر أكتوبر")
            $table->text('description')->nullable();
            $table->enum('period', ['daily', 'weekly', 'monthly', 'yearly', 'custom'])->default('monthly');
            $table->decimal('total_limit', 10, 2); // الحد الكلي للميزانية
            $table->decimal('spent_amount', 10, 2)->default(0); // المبلغ المصروف
            $table->decimal('remaining_amount', 10, 2)->default(0); // المبلغ المتبقي
            $table->date('start_date'); // تاريخ البداية
            $table->date('end_date'); // تاريخ النهاية
            $table->integer('alert_percentage')->default(80); // نسبة التنبيه (80% = تنبيه عند 80%)
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_renew')->default(false); // تجديد تلقائي بعد انتهاء الفترة
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
