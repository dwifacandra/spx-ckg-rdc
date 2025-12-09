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
        Schema::create('assets_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('ops_id');
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->timestamp('check_in')->useCurrent();
            $table->timestamp('check_out')->nullable();
            $table->string('created_by');
            $table->enum('status', ['in use', 'complete', 'overtime'])->default('in use');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets_transactions');
    }
};
