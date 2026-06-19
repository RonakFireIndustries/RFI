<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function dropForeignIfExists(string $table, string $column): void
    {
        $fkName = str_replace(['-', '/'], '_', $table) . '_' . $column . '_foreign';
        try {
            Schema::table($table, function (Blueprint $t) use ($fkName) {
                $t->dropForeign($fkName);
            });
        } catch (\Exception $e) {
            // FK may not exist
        }
    }

    public function up(): void
    {
        Schema::dropIfExists('inventory_locations');

        $this->dropForeignIfExists('product_stock', 'product_id');
        $this->dropForeignIfExists('product_stock', 'location_id');
        Schema::table('product_stock', function (Blueprint $table) {
            if (!Schema::hasColumn('product_stock', 'location_type')) {
                $table->string('location_type')->default('App\\Models\\Branch');
            }
        });

        $this->dropForeignIfExists('transaction_ledger', 'product_id');
        $this->dropForeignIfExists('transaction_ledger', 'location_id');
        $this->dropForeignIfExists('transaction_ledger', 'to_location_id');
        $this->dropForeignIfExists('transaction_ledger', 'created_by');
        Schema::table('transaction_ledger', function (Blueprint $table) {
            if (!Schema::hasColumn('transaction_ledger', 'location_type')) {
                $table->string('location_type')->default('App\\Models\\Branch');
            }
            if (!Schema::hasColumn('transaction_ledger', 'to_location_type')) {
                $table->string('to_location_type')->nullable();
            }
        });

        $this->dropForeignIfExists('stock_requests', 'product_id');
        $this->dropForeignIfExists('stock_requests', 'from_location_id');
        $this->dropForeignIfExists('stock_requests', 'to_location_id');
        $this->dropForeignIfExists('stock_requests', 'requested_by');
        $this->dropForeignIfExists('stock_requests', 'approved_by');
        $this->dropForeignIfExists('stock_requests', 'issued_by');
        $this->dropForeignIfExists('stock_requests', 'received_by');
        Schema::table('stock_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_requests', 'from_location_type')) {
                $table->string('from_location_type')->default('App\\Models\\Branch');
            }
            if (!Schema::hasColumn('stock_requests', 'to_location_type')) {
                $table->string('to_location_type')->default('App\\Models\\Branch');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_stock', function (Blueprint $table) {
            if (Schema::hasColumn('product_stock', 'location_type')) {
                $table->dropColumn('location_type');
            }
        });

        Schema::table('transaction_ledger', function (Blueprint $table) {
            if (Schema::hasColumn('transaction_ledger', 'location_type')) {
                $table->dropColumn('location_type');
            }
            if (Schema::hasColumn('transaction_ledger', 'to_location_type')) {
                $table->dropColumn('to_location_type');
            }
        });

        Schema::table('stock_requests', function (Blueprint $table) {
            if (Schema::hasColumn('stock_requests', 'from_location_type')) {
                $table->dropColumn('from_location_type');
            }
            if (Schema::hasColumn('stock_requests', 'to_location_type')) {
                $table->dropColumn('to_location_type');
            }
        });
    }
};
