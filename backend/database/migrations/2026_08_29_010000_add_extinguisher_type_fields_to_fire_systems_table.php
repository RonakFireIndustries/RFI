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
            $table->string('type')->nullable()->after('sub_type');
            $table->integer('year_of_manufacturing')->nullable()->after('installation_year');
            $table->text('remark')->nullable()->after('next_refill_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fire_systems', function (Blueprint $table) {
            $table->dropColumn(['type', 'year_of_manufacturing', 'remark']);
        });
    }
};