<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Convert existing string values to integers where possible
        DB::table('buildings')->whereRaw("no_of_wings REGEXP '^[0-9]+$'")
            ->update(['no_of_wings' => DB::raw('CAST(no_of_wings AS UNSIGNED)')]);

        // Clear non-numeric values
        DB::table('buildings')->whereRaw("no_of_wings IS NOT NULL AND no_of_wings NOT REGEXP '^[0-9]+$'")
            ->update(['no_of_wings' => null]);

        Schema::table('buildings', function (Blueprint $table) {
            $table->unsignedInteger('no_of_wings')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('buildings', function (Blueprint $table) {
            $table->string('no_of_wings', 50)->nullable()->change();
        });
    }
};
