<?php

use App\Models\Account;
use App\Models\Team;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $needsNestedSet = ! Schema::hasColumn('accounts', '_lft');

        if ($needsNestedSet) {
            Schema::table('accounts', function (Blueprint $table) {
                $table->unsignedInteger('_lft')->default(0)->after('parent_id');
                $table->unsignedInteger('_rgt')->default(0)->after('_lft');
                $table->index(['_lft', '_rgt', 'parent_id']);
            });
        }

        if (Schema::hasTable('accounts') && Account::query()->exists()) {
            foreach (Team::all() as $team) {
                Account::scoped(['team_id' => $team->id])->fixTree();
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('accounts', '_lft')) {
            return;
        }

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropIndex(['_lft', '_rgt', 'parent_id']);
            $table->dropColumn(['_lft', '_rgt']);
        });
    }
};
