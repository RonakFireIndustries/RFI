<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('stock_requests')) {
            Schema::create('stock_requests', function (Blueprint $table) {
                $table->id();
                $table->string('request_number')->unique();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('from_location_id');
                $table->unsignedBigInteger('to_location_id');
                $table->decimal('quantity', 12, 2);
                $table->decimal('approved_quantity', 12, 2)->default(0);
                $table->decimal('issued_quantity', 12, 2)->default(0);
                $table->decimal('received_quantity', 12, 2)->default(0);
                $table->enum('status', ['requested', 'approved', 'issued', 'received'])->default('requested');
                $table->unsignedBigInteger('requested_by');
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->unsignedBigInteger('issued_by')->nullable();
                $table->unsignedBigInteger('received_by')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });

            try {
                Schema::table('stock_requests', function (Blueprint $table) {
                    $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
                    $table->foreign('from_location_id')->references('id')->on('inventory_locations')->cascadeOnDelete();
                    $table->foreign('to_location_id')->references('id')->on('inventory_locations')->cascadeOnDelete();
                    $table->foreign('requested_by')->references('id')->on('users')->cascadeOnDelete();
                    $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
                    $table->foreign('issued_by')->references('id')->on('users')->nullOnDelete();
                    $table->foreign('received_by')->references('id')->on('users')->nullOnDelete();
                });
            } catch (\Exception $e) {
                // FK constraints optional if engine mismatch
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_requests');
    }
};
