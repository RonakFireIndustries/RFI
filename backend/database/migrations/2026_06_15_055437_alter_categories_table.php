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
        Schema::table('categories', function (Blueprint $table) {
            $table->string('status')->default('Active'); // Active, Inactive
            $table->foreignId('parent_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade'); // Null means global
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['created_by']);
            $table->dropColumn(['status', 'parent_id', 'branch_id', 'created_by']);
        });
    }
};
