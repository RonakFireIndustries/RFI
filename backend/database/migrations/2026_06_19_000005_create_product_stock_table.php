<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_stock')) {
            Schema::create('product_stock', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('location_id');
                $table->decimal('quantity', 12, 2)->default(0);
                $table->decimal('reserved_quantity', 12, 2)->default(0);
                $table->decimal('available_quantity', 12, 2)->default(0);
                $table->timestamps();

                $table->unique(['product_id', 'location_id']);
            });
        }

        try {
            Schema::table('product_stock', function (Blueprint $table) {
                $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
                $table->foreign('location_id')->references('id')->on('inventory_locations')->cascadeOnDelete();
            });
        } catch (\Exception $e) {
            // Foreign keys may fail if engines differ; app handles integrity
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_stock');
    }
};
