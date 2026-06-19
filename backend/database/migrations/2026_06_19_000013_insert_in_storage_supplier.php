<?php

use App\Models\Supplier;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Supplier::firstOrCreate(
            ['name' => 'InStorage'],
            [
                'email' => 'instorage@system.local',
                'phone' => '0000000000',
                'address' => 'System - Internal Storage',
                'is_system' => true,
            ]
        );
    }

    public function down(): void
    {
        Supplier::where('name', 'InStorage')->delete();
    }
};
