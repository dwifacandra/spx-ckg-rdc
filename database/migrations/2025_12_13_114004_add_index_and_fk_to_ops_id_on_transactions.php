<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('assets_transactions', function (Blueprint $table) {
            $table->index('ops_id');
        });

        DB::statement('ALTER TABLE assets_transactions DROP FOREIGN KEY IF EXISTS assets_transactions_ops_id_foreign');
        DB::statement('
            ALTER TABLE assets_transactions
            ADD CONSTRAINT assets_transactions_ops_id_foreign
            FOREIGN KEY (ops_id)
            REFERENCES employees (ops_id)
            ON DELETE CASCADE
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE assets_transactions DROP FOREIGN KEY IF EXISTS assets_transactions_ops_id_foreign');
        Schema::table('assets_transactions', function (Blueprint $table) {
            $table->dropIndex(['ops_id']);
        });
    }
};
