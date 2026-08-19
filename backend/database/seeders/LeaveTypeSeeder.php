<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeaveType;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure only Paid Leave (21) and Sick Leave (4) exist - nothing more
        LeaveType::updateOrCreate(
            ['code' => 'PL'],
            [
                'name' => 'Paid Leave',
                'annual_allocation' => 21,
                'carry_forward' => false,
                'max_consecutive_days' => null,
                'requires_approval' => true,
                'status' => 'Active',
            ]
        );

        LeaveType::updateOrCreate(
            ['code' => 'SL'],
            [
                'name' => 'Sick Leave',
                'annual_allocation' => 4,
                'carry_forward' => false,
                'max_consecutive_days' => null,
                'requires_approval' => true,
                'status' => 'Active',
            ]
        );

        // Remove all other leave types
        LeaveType::whereNotIn('code', ['PL', 'SL'])->delete();
    }
}
