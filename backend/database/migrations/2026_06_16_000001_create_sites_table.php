<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sites')) {
            Schema::create('sites', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('client_details')->nullable();
                $table->string('status')->default('Active');
                $table->unsignedBigInteger('site_manager_id')->nullable();
                $table->timestamps();

                $table->foreign('site_manager_id')->references('id')->on('employees')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
