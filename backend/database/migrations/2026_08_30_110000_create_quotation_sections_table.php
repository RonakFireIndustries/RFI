<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quotation_id')->index();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('quotation_id')->references('id')->on('quotations')->cascadeOnDelete();
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->unsignedBigInteger('quotation_section_id')->nullable()->after('quotation_id')->index();
            $table->foreign('quotation_section_id')
                ->references('id')->on('quotation_sections')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropForeign(['quotation_section_id']);
            $table->dropColumn('quotation_section_id');
        });

        Schema::dropIfExists('quotation_sections');
    }
};
