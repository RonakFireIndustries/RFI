<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeaveType;

class LeaveTypeSeeder extends Seeder
{
    /**
     * The organisation's leave catalogue:
     *
     *  - Sick / Casual  are available to every employee.
     *  - Maternity      is available to female employees only.
     *  - Paternity      is available to male employees only.
     *
     * Maternity and Paternity are capped at 10 days and never carry forward.
     * Existing leave types are updated in place - nothing is deleted.
     */
    public function run(): void
    {
        $types = [
            [
                'code' => 'SL',
                'name' => 'Sick Leave',
                'annual_allocation' => 10,
                'carry_forward' => false,
                'max_consecutive_days' => 3,
                'requires_approval' => false,
                'gender_restriction' => null,
            ],
            [
                'code' => 'CL',
                'name' => 'Casual Leave',
                'annual_allocation' => 7,
                'carry_forward' => false,
                'max_consecutive_days' => 2,
                'requires_approval' => true,
                'gender_restriction' => null,
            ],
            [
                'code' => 'ML',
                'name' => 'Maternity Leave',
                'annual_allocation' => 10,
                'carry_forward' => false,
                'max_consecutive_days' => 10,
                'requires_approval' => true,
                'gender_restriction' => 'Female',
            ],
            [
                'code' => 'PTL',
                'name' => 'Paternity Leave',
                'annual_allocation' => 10,
                'carry_forward' => false,
                'max_consecutive_days' => 10,
                'requires_approval' => true,
                'gender_restriction' => 'Male',
            ],
        ];

        foreach ($types as $type) {
            LeaveType::updateOrCreate(
                ['code' => $type['code']],
                $type + ['status' => 'Active']
            );
        }
    }
}
