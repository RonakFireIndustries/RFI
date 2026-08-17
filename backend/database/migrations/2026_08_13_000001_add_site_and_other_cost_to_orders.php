<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('site_id')->nullable()->after('supplier_id')->constrained('sites')->nullOnDelete();
            $table->decimal('other_cost', 12, 2)->default(0)->after('shipping_cost');
            $table->string('other_cost_note')->nullable()->after('other_cost');
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->foreignId('site_id')->nullable()->after('customer_id')->constrained('sites')->nullOnDelete();
            $table->decimal('other_cost', 12, 2)->default(0)->after('shipping_cost');
            $table->string('other_cost_note')->nullable()->after('other_cost');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['site_id']);
            $table->dropColumn(['site_id', 'other_cost', 'other_cost_note']);
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropForeign(['site_id']);
            $table->dropColumn(['site_id', 'other_cost', 'other_cost_note']);
        });
    }
};
