<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('original_file_name')->nullable()->after('file_name');
            $table->string('mime_type')->nullable()->after('original_file_name');
            $table->unsignedBigInteger('file_size')->nullable()->after('mime_type')->comment('in bytes');
            $table->string('document_type')->nullable()->after('file_type')->comment('e.g. Resume, Offer Letter');
            $table->date('expiry_date')->nullable()->after('document_type');
            $table->text('remarks')->nullable()->after('expiry_date');
            $table->softDeletes()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn([
                'original_file_name',
                'mime_type',
                'file_size',
                'document_type',
                'expiry_date',
                'remarks',
                'deleted_at'
            ]);
        });
    }
};
