<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'checkin_latitude')) {
                $table->decimal('checkin_latitude', 10, 8)->nullable()->after('check_out');
            }
            if (!Schema::hasColumn('attendances', 'checkin_longitude')) {
                $table->decimal('checkin_longitude', 11, 8)->nullable()->after('checkin_latitude');
            }
            if (!Schema::hasColumn('attendances', 'checkout_latitude')) {
                $table->decimal('checkout_latitude', 10, 8)->nullable()->after('checkin_longitude');
            }
            if (!Schema::hasColumn('attendances', 'checkout_longitude')) {
                $table->decimal('checkout_longitude', 11, 8)->nullable()->after('checkout_latitude');
            }
            if (!Schema::hasColumn('attendances', 'checkin_distance')) {
                $table->decimal('checkin_distance', 8, 2)->nullable()->after('checkout_longitude');
            }
            if (!Schema::hasColumn('attendances', 'checkout_distance')) {
                $table->decimal('checkout_distance', 8, 2)->nullable()->after('checkin_distance');
            }
            if (!Schema::hasColumn('attendances', 'location_verified')) {
                $table->boolean('location_verified')->default(false)->after('checkout_distance');
            }
            if (!Schema::hasColumn('attendances', 'accuracy')) {
                $table->decimal('accuracy', 8, 2)->nullable()->after('location_verified');
            }
            if (!Schema::hasColumn('attendances', 'device_info')) {
                $table->string('device_info')->nullable()->after('accuracy');
            }
            if (!Schema::hasColumn('attendances', 'browser_info')) {
                $table->string('browser_info')->nullable()->after('device_info');
            }
            if (!Schema::hasColumn('attendances', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('browser_info');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'checkin_latitude', 'checkin_longitude',
                'checkout_latitude', 'checkout_longitude',
                'checkin_distance', 'checkout_distance',
                'location_verified', 'accuracy',
                'device_info', 'browser_info', 'ip_address',
            ]);
        });
    }
};
