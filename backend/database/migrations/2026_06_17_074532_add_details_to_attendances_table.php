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
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'site_id')) {
                $table->foreignId('site_id')->nullable()->constrained('sites')->onDelete('set null');
            }
            if (!Schema::hasColumn('attendances', 'shift_id')) {
                $table->foreignId('shift_id')->nullable()->constrained('shifts')->onDelete('set null');
            }
            if (!Schema::hasColumn('attendances', 'working_hours')) {
                $table->decimal('working_hours', 8, 2)->default(0.00);
            }
            if (!Schema::hasColumn('attendances', 'overtime_hours')) {
                $table->decimal('overtime_hours', 8, 2)->default(0.00);
            }
            if (!Schema::hasColumn('attendances', 'remarks')) {
                $table->text('remarks')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'site_id')) {
                $table->dropForeign(['site_id']);
                $table->dropColumn(['site_id']);
            }
            if (Schema::hasColumn('attendances', 'shift_id')) {
                $table->dropForeign(['shift_id']);
                $table->dropColumn(['shift_id']);
            }
            if (Schema::hasColumn('attendances', 'working_hours')) {
                $table->dropColumn(['working_hours']);
            }
            if (Schema::hasColumn('attendances', 'overtime_hours')) {
                $table->dropColumn(['overtime_hours']);
            }
            if (Schema::hasColumn('attendances', 'remarks')) {
                $table->dropColumn(['remarks']);
            }
        });
    }
};
