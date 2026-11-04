<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'checkout_site_id')) {
                $table->foreignId('checkout_site_id')
                    ->nullable()
                    ->after('site_id')
                    ->constrained('sites')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'checkout_site_id')) {
                $table->dropConstrainedForeignId('checkout_site_id');
            }
        });
    }
};
