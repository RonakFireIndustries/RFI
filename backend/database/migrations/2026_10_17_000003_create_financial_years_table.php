<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Financial years, e.g. "2025-2026". Exactly one year is active at a time;
     * new vouchers default to the active year and reports are framed around it.
     */
    public function up(): void
    {
        if (Schema::hasTable('financial_years')) {
            return;
        }

        Schema::create('financial_years', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_closed')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_years');
    }
};
