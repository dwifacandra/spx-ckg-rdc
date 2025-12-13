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
    {        // --- 1. assets_transactions_ops_id_foreign (Ke Tabel 'employees') ---
        DB::statement('ALTER TABLE assets_transactions DROP FOREIGN KEY IF EXISTS assets_transactions_ops_id_foreign');
        DB::statement('
            ALTER TABLE assets_transactions
            ADD CONSTRAINT assets_transactions_ops_id_foreign
            FOREIGN KEY (ops_id)
            REFERENCES employees (ops_id)
            ON DELETE RESTRICT
            ON UPDATE CASCADE
        ');

        // --- 2. assets_transactions_asset_id_foreign (Ke Tabel 'assets') ---
        DB::statement('ALTER TABLE assets_transactions DROP FOREIGN KEY IF EXISTS assets_transactions_asset_id_foreign');
        DB::statement('
            ALTER TABLE assets_transactions
            ADD CONSTRAINT assets_transactions_asset_id_foreign
            FOREIGN KEY (asset_id)
            REFERENCES assets (code)
            ON DELETE RESTRICT
            ON UPDATE CASCADE
        ');

        // --- 3. assets_transactions_created_by_foreign (Ke Tabel 'users') ---
        DB::statement('ALTER TABLE assets_transactions DROP FOREIGN KEY IF EXISTS assets_transactions_created_by_foreign');
        DB::statement('
            ALTER TABLE assets_transactions
            ADD CONSTRAINT assets_transactions_created_by_foreign
            FOREIGN KEY (created_by)
            REFERENCES users (id)
            ON DELETE RESTRICT
            ON UPDATE CASCADE
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Untuk rollback, kita hapus semua FK agar bisa dibuat ulang dengan aksi default (atau aksi sebelumnya)

        // Hapus semua Foreign Key
        DB::statement('ALTER TABLE assets_transactions DROP FOREIGN KEY IF EXISTS assets_transactions_ops_id_foreign');
        DB::statement('ALTER TABLE assets_transactions DROP FOREIGN KEY IF EXISTS assets_transactions_asset_id_foreign');
        DB::statement('ALTER TABLE assets_transactions DROP FOREIGN KEY IF EXISTS assets_transactions_created_by_foreign');

        // Buat ulang dengan aksi default (misalnya, ON DELETE CASCADE/RESTRICT tanpa ON UPDATE)

        // Catatan: Karena menggunakan Raw SQL, kita harus mendefinisikan ulang semuanya di down() juga.

        // Re-create ops_id (Kembali ke ON DELETE CASCADE, tanpa ON UPDATE)
        DB::statement('
            ALTER TABLE assets_transactions
            ADD CONSTRAINT assets_transactions_ops_id_foreign
            FOREIGN KEY (ops_id)
            REFERENCES employees (ops_id)
            ON DELETE CASCADE
        ');

        // Re-create asset_id
        DB::statement('
            ALTER TABLE assets_transactions
            ADD CONSTRAINT assets_transactions_asset_id_foreign
            FOREIGN KEY (asset_id)
            REFERENCES assets (code)
            ON DELETE CASCADE
        ');

        // Re-create created_by (Kembali ke ON DELETE RESTRICT)
        DB::statement('
            ALTER TABLE assets_transactions
            ADD CONSTRAINT assets_transactions_created_by_foreign
            FOREIGN KEY (created_by)
            REFERENCES users (id)
            ON DELETE RESTRICT
        ');
    }
};
