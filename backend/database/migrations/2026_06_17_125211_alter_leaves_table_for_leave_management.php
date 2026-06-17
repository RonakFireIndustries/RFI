<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            // Drop old string-based leave_type column
            $table->dropColumn('leave_type');

            // Add new relations and fields
            $table->foreignId('leave_type_id')->nullable()->after('employee_id')->constrained()->nullOnDelete();
            $table->decimal('total_days', 5, 2)->default(1)->after('end_date');
            $table->string('attachment_path')->nullable()->after('reason');
            $table->text('comments')->nullable()->after('attachment_path');
            $table->timestamp('applied_at')->nullable()->after('comments');
        });
    }

    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->string('leave_type')->nullable();
            $table->dropForeign(['leave_type_id']);
            $table->dropColumn(['leave_type_id', 'total_days', 'attachment_path', 'comments', 'applied_at']);
        });
    }
};
