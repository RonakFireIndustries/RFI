<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('product_code')->nullable()->after('sku');
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete()->after('category_id');
            $table->decimal('cost_price', 10, 2)->default(0)->after('selling_price');
            $table->decimal('reorder_level', 10, 2)->default(0)->after('cost_price');
            $table->decimal('min_stock', 10, 2)->default(0)->after('reorder_level');
            $table->decimal('max_stock', 10, 2)->default(0)->after('min_stock');
            $table->text('description')->nullable()->after('max_stock');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['product_code', 'unit_id', 'cost_price', 'reorder_level', 'min_stock', 'max_stock', 'description']);
        });
    }
};
