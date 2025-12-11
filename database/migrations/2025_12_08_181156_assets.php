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
        Schema::create('assets', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->string('item');
            $table->string('brand')->nullable();
            $table->string('type')->nullable();
            $table->string('tag')->nullable();
            $table->string('serial_number')->unique()->nullable();
            $table->enum('condition', ['good', 'damaged', 'lost'])->nullable()->default('good');
            $table->enum('status', ['in use', 'standby', 'lost', 'repair'])->nullable()->default('in use');
            $table->text('remarks')->nullable();
            $table->string('ownership')->nullable()->comment('email of owner');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
