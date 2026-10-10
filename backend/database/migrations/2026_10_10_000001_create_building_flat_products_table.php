<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Free-text product details captured per flat (room), typically the fire-safety
 * or MEP equipment installed in that unit. This is a post-conversion feature:
 * products are only ever recorded once a building has been promoted to a site,
 * so `site_id` accompanies the row for reporting alongside the mirrored survey
 * children.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('building_flat_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained()->cascadeOnDelete();
            $table->foreignId('flat_id')->constrained('building_flats')->cascadeOnDelete();
            $table->unsignedBigInteger('site_id')->nullable()->index();
            $table->string('product_name');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->integer('quantity')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('site_id')->references('id')->on('sites')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('building_flat_products');
    }
};
