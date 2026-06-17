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
        Schema::table('employees', function (Blueprint $table) {
            $table->boolean('employment_bond_status')->nullable()->change();
            $table->boolean('previous_termination_status')->nullable()->change();
            $table->boolean('legal_proceedings_status')->nullable()->change();
            if (Schema::hasColumn('employees', 'create_user_account')) {
                $table->boolean('create_user_account')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->boolean('employment_bond_status')->nullable(false)->change();
            $table->boolean('previous_termination_status')->nullable(false)->change();
            $table->boolean('legal_proceedings_status')->nullable(false)->change();
            if (Schema::hasColumn('employees', 'create_user_account')) {
                $table->boolean('create_user_account')->nullable(false)->change();
            }
        });
    }
};
