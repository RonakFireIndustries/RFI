<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained()->cascadeOnDelete();
            $table->decimal('allocated', 5, 2)->default(0);
            $table->decimal('used', 5, 2)->default(0);
            $table->decimal('remaining', 5, 2)->default(0);
            $table->decimal('carry_forward', 5, 2)->default(0);
            $table->decimal('expired', 5, 2)->default(0);
            $table->year('year');
            $table->timestamps();

            $table->unique(['employee_id', 'leave_type_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_leave_balances');
    }
};
