<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('attendances', 'is_auto_checkout')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->boolean('is_auto_checkout')->default(false)->after('check_out');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('attendances', 'is_auto_checkout')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropColumn('is_auto_checkout');
            });
        }
    }
};
