<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('site_visit_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('opportunity_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category');
            $table->string('file_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('file_path');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('expiry_date')->nullable();
            $table->integer('version_number')->default(1);
            $table->foreignId('parent_document_id')->nullable()->constrained('sales_documents')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_documents');
    }
};
