<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('employee_shifts')) {
            Schema::create('employee_shifts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->foreignId('shift_id')->constrained('shifts')->onDelete('cascade');
                $table->timestamp('assigned_at')->nullable();
                $table->string('remarks')->nullable();
                $table->timestamps();

                // One active shift per employee. Changing a shift replaces this
                // row and is recorded in employee_shift_histories.
                $table->unique('employee_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_shifts');
    }
};
