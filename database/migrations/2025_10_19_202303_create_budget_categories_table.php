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
        Schema::create('budget_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('budget_id')->nullable()->constrained('budgets')->cascadeOnDelete();
            $table->string('name'); // اسم الفئة (بقالة، مطاعم، ملابس، أدوية، إلخ)
            $table->text('description')->nullable();
            $table->decimal('budget_limit', 10, 2); // حد الميزانية لهذه الفئة
            $table->decimal('spent_amount', 10, 2)->default(0); // المبلغ المصروف
            $table->string('icon')->nullable(); // أيقونة الفئة
            $table->string('color')->default('#3b82f6'); // لون الفئة
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0); // ترتيب العرض
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_categories');
    }
};
