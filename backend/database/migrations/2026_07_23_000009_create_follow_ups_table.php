<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('opportunity_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('site_visit_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('building_contact_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('reminder_date');
            $table->time('reminder_time')->nullable();
            $table->string('type');
            $table->text('notes')->nullable();
            $table->string('status')->default('Pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_ups');
    }
};
