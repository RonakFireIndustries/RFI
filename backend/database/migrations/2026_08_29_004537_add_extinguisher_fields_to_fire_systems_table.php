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
        Schema::table('fire_systems', function (Blueprint $table) {
            $table->string('label')->nullable()->after('sub_type');
            $table->date('installation_date')->nullable()->after('brand');
            $table->date('next_refill_date')->nullable()->after('installation_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fire_systems', function (Blueprint $table) {
            $table->dropColumn(['label', 'installation_date', 'next_refill_date']);
        });
    }
};
