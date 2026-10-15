<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds the per-structure switch that decides whether PF and ESIC are
     * derived from the statutory rule (default) or taken from the manually
     * entered amounts.
     */
    public function up(): void
    {
        if (Schema::hasColumn('salary_structures', 'auto_statutory')) {
            return;
        }

        Schema::table('salary_structures', function (Blueprint $table) {
            $table->boolean('auto_statutory')->default(true)->after('other_deductions');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('salary_structures', 'auto_statutory')) {
            return;
        }

        Schema::table('salary_structures', function (Blueprint $table) {
            $table->dropColumn('auto_statutory');
        });
    }
};
