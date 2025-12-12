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
        Schema::create('access_card_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('access_card_id')->constrained()->onDelete('cascade');
            $table->string('ops_id');
            $table->foreign('ops_id')
                ->references('ops_id')->on('employees')
                ->onDelete('restrict');
            $table->timestamp('check_out')->nullable();
            $table->timestamp('check_in')->nullable();
            $table->enum('status', ['in use', 'complete', 'overtime', 'lost'])->default('in use');
            $table->string('location')->nullable();
            $table->text('remarks')->nullable();
            $table->index(['access_card_id', 'ops_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_card_transactions');
    }
};
