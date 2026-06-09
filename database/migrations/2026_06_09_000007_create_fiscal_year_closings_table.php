<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiscal_year_closings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->smallInteger('fiscal_year');
            $table->date('closing_date');
            $table->string('status')->default('draft'); // draft|posted|locked
            $table->decimal('total_revenue', 14, 2)->default(0);
            $table->decimal('total_expense', 14, 2)->default(0);
            $table->decimal('net_income', 14, 2)->default(0);   // revenue - expense
            $table->decimal('retained_earnings_before', 14, 2)->default(0);
            $table->decimal('retained_earnings_after', 14, 2)->default(0);
            $table->foreignId('journal_entry_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'fiscal_year']); // إغلاق واحد فقط لكل سنة
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiscal_year_closings');
    }
};
