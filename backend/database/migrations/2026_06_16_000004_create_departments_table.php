<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('departments')) {
            Schema::create('departments', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('description')->nullable();
                $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
                $table->unsignedBigInteger('head_id')->nullable();
                $table->string('status')->default('Active');
                $table->timestamps();

                $table->foreign('head_id')->references('id')->on('employees')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
