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
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('leave_type'); // Casual, Sick, Maternity, etc.
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_half_day')->default(false);
            $table->string('status')->default('Pending'); // Pending, Approved, Rejected
            $table->foreignId('approved_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};
