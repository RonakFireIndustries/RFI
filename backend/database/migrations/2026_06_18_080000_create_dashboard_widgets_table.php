<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->string('widget_key')->unique();
            $table->string('name');
            $table->string('type'); // card, chart, quick_action, alert
            $table->string('icon')->nullable();
            $table->string('chart_type')->nullable(); // area, bar, line, pie
            $table->json('config')->nullable();
            $table->string('permission')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('dashboard_widget_designation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dashboard_widget_id')->constrained()->cascadeOnDelete();
            $table->foreignId('designation_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_widget_designation');
        Schema::dropIfExists('dashboard_widgets');
    }
};
