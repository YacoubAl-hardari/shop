<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('count_number')->unique();
            $table->date('count_date');
            $table->smallInteger('fiscal_year');
            $table->string('status')->default('draft'); // draft|in_progress|completed|approved
            $table->decimal('total_book_value', 14, 2)->default(0);
            $table->decimal('total_counted_value', 14, 2)->default(0);
            $table->decimal('variance_value', 14, 2)->default(0);   // counted - book
            $table->foreignId('journal_entry_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'fiscal_year', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_counts');
    }
};
