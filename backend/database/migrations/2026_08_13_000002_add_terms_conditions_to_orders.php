<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->text('terms_conditions')->nullable()->after('notes');
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->text('terms_conditions')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('terms_conditions');
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn('terms_conditions');
        });
    }
};
