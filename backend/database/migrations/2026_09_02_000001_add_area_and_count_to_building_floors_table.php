<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('building_floors', function (Blueprint $table) {
            $table->decimal('area', 10, 2)->nullable()->comment('Area in sqft for parking/commercial floors');
            $table->integer('count')->unsigned()->nullable()->comment('Number of parking spots / shops on this floor');
        });
    }

    public function down(): void
    {
        Schema::table('building_floors', function (Blueprint $table) {
            $table->dropColumn(['area', 'count']);
        });
    }
};
