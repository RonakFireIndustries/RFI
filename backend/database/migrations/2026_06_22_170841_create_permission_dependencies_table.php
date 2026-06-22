<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('depends_on_permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->string('type')->default('requires');
            $table->timestamps();

            $table->unique(['permission_id', 'depends_on_permission_id'], 'pd_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_dependencies');
    }
};
