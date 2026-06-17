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
        Schema::create('employee_site_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('assigned_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('previous_site_id')->nullable()->constrained('sites')->onDelete('set null');
            $table->foreignId('new_site_id')->nullable()->constrained('sites')->onDelete('set null');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('transfer_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_site_histories');
    }
};
