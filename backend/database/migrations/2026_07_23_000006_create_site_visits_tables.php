<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('visit_date');
            $table->string('purpose')->nullable();
            $table->text('discussion_notes')->nullable();
            $table->date('next_followup_date')->nullable();
            $table->timestamps();
        });

        Schema::create('site_visit_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_visit_id')->constrained()->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('site_visit_voice_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_visit_id')->constrained()->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_visit_voice_notes');
        Schema::dropIfExists('site_visit_photos');
        Schema::dropIfExists('site_visits');
    }
};
