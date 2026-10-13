<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('employee_shift_histories')) {
            Schema::create('employee_shift_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->foreignId('assigned_by_id')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('previous_shift_id')->nullable()->constrained('shifts')->onDelete('set null');
                $table->foreignId('new_shift_id')->nullable()->constrained('shifts')->onDelete('set null');
                $table->timestamp('assigned_at')->nullable();
                $table->timestamp('effective_date')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_shift_histories');
    }
};
