<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fire_systems', function (Blueprint $table) {
            $table->string('status')->default('Available')->after('sub_type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('fire_systems', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn('status');
        });
    }
};
