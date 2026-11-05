<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Insert the internal "InStorage" party. It is still written to the
     * suppliers table here because this migration runs before the party
     * masters are converted into ledger accounts; the conversion migration
     * then turns it into a system ledger with is_system = true.
     */
    public function up(): void
    {
        if (! DB::table('suppliers')->where('name', 'InStorage')->exists()) {
            DB::table('suppliers')->insert([
                'name' => 'InStorage',
                'email' => 'instorage@system.local',
                'phone' => '0000000000',
                'address' => 'System - Internal Storage',
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('suppliers')->where('name', 'InStorage')->delete();
    }
};
