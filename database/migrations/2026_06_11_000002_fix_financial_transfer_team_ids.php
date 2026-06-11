<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            UPDATE merchant_customer_financial_transfers AS t
            INNER JOIN merchant_customer_statement_shares AS s ON t.statement_share_id = s.id
            SET t.team_id = s.team_id
            WHERE t.team_id != s.team_id
        ');

        DB::statement('
            UPDATE merchant_customer_financial_transfers AS t
            INNER JOIN merchant_customers AS c ON t.merchant_customer_id = c.id
            SET t.team_id = c.team_id
            WHERE t.statement_share_id IS NULL AND t.team_id != c.team_id
        ');
    }

    public function down(): void
    {
        // Irreversible data correction
    }
};
