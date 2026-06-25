<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_orders', 'received_by')) {
                $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete()->after('approved_by');
            }
            if (!Schema::hasColumn('purchase_orders', 'received_at')) {
                $table->timestamp('received_at')->nullable()->after('received_by');
            }
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_orders', 'delivered_by')) {
                $table->foreignId('delivered_by')->nullable()->constrained('users')->nullOnDelete()->after('approved_by');
            }
            if (!Schema::hasColumn('sales_orders', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('delivered_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['received_by']);
            $table->dropColumn(['received_by', 'received_at']);
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropForeign(['delivered_by']);
            $table->dropColumn(['delivered_by', 'delivered_at']);
        });
    }
};
