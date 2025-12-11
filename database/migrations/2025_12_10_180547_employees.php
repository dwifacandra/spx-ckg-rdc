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
        Schema::create('employees', function (Blueprint $table) {
            $table->string('ops_id')->primary();
            $table->string('staff_name')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('passport_id')->nullable();
            $table->string('employee_id')->nullable();
            $table->boolean('blocklist')->default(false);
            $table->string('contract_type')->nullable();
            $table->date('joined_date')->nullable();
            $table->date('last_date')->nullable();
            $table->string('agency')->nullable();
            $table->string('department')->nullable();
            $table->string('station')->nullable();
            $table->enum('ops_status', ['active', 'inactive', 'on leave', 'temperory'])->default('active');
            $table->string('email')->nullable();
            $table->string('soup_role')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
