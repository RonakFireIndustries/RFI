<?php

use App\Models\Supplier;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'opening_stock')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('opening_stock', 15, 2)->default(0)->after('status');
            });
        }

        if (!Schema::hasColumn('suppliers', 'is_system')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->boolean('is_system')->default(false)->after('address');
            });
        }

    }

    public function down(): void
    {
        if (Schema::hasColumn('suppliers', 'is_system')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->dropColumn('is_system');
            });
        }

        if (Schema::hasColumn('products', 'opening_stock')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('opening_stock');
            });
        }
    }
};
