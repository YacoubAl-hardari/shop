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
        Schema::create('budget_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('budget_id')->nullable()->constrained('budgets')->cascadeOnDelete();
            $table->foreignId('budget_category_id')->nullable()->constrained('budget_categories')->cascadeOnDelete();
            $table->enum('type', ['warning', 'danger', 'exceeded', 'info']); // نوع التنبيه
            $table->string('title'); // عنوان التنبيه
            $table->text('message'); // رسالة التنبيه
            $table->decimal('trigger_percentage', 5, 2)->nullable(); // النسبة التي أدت للتنبيه
            $table->decimal('current_amount', 10, 2); // المبلغ الحالي عند التنبيه
            $table->boolean('is_read')->default(false); // هل تمت قراءة التنبيه
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_alerts');
    }
};
