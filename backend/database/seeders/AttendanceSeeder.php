<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = \App\Models\Employee::all();
        if ($employees->isEmpty()) {
            return;
        }

        $startDate = now()->startOfMonth();
        $endDate = now();

        foreach ($employees as $employee) {
            for ($date = clone $startDate; $date->lte($endDate); $date->addDay()) {
                if ($date->isWeekend()) {
                    continue;
                }

                $statusRoll = rand(1, 100);
                $status = 'present';
                $checkIn = null;
                $checkOut = null;

                if ($statusRoll > 95) {
                    $status = 'absent';
                } elseif ($statusRoll > 90) {
                    $status = 'leave';
                } elseif ($statusRoll > 80) {
                    $status = 'late';
                    $checkIn = $date->copy()->setTime(rand(9, 10), rand(15, 59));
                    $checkOut = $date->copy()->setTime(rand(17, 18), rand(0, 30));
                } else {
                    $checkIn = $date->copy()->setTime(rand(8, 9), rand(0, 15));
                    $checkOut = $date->copy()->setTime(rand(17, 18), rand(0, 30));
                }

                \App\Models\Attendance::create([
                    'employee_id' => $employee->id,
                    'date' => $date->toDateString(),
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'status' => $status,
                ]);
            }
        }
    }
}
