<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_settings', function (Blueprint $table) {
            $table->id();
            $table->string('configurable_type');
            $table->unsignedBigInteger('configurable_id');
            $table->string('logging_verbosity')->default('medium');
            $table->integer('retention_days')->default(90);
            $table->boolean('reason_required')->default(false);
            $table->boolean('change_tracking_enabled')->default(true);
            $table->boolean('snapshot_creation_enabled')->default(false);
            $table->timestamps();

            $table->index(['configurable_type', 'configurable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_settings');
    }
};
