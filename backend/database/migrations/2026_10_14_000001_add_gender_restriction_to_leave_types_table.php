<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('leave_types', 'gender_restriction')) {
            Schema::table('leave_types', function (Blueprint $table) {
                $table->string('gender_restriction')->nullable()->after('max_consecutive_days');
            });
        }

        // Maternity: 10 days, max 10 consecutive, no carry forward, Female only.
        DB::table('leave_types')->where('code', 'ML')->update([
            'name' => 'Maternity Leave',
            'annual_allocation' => 10,
            'max_consecutive_days' => 10,
            'carry_forward' => false,
            'gender_restriction' => 'Female',
            'updated_at' => now(),
        ]);

        // Paternity: 10 days, max 10 consecutive, no carry forward, Male only.
        $paternityValues = [
            'name' => 'Paternity Leave',
            'annual_allocation' => 10,
            'carry_forward' => false,
            'max_consecutive_days' => 10,
            'requires_approval' => true,
            'gender_restriction' => 'Male',
            'updated_at' => now(),
        ];

        if (DB::table('leave_types')->where('code', 'PTL')->exists()) {
            DB::table('leave_types')->where('code', 'PTL')->update($paternityValues);
        } else {
            DB::table('leave_types')->insert(array_merge($paternityValues, [
                'code' => 'PTL',
                'created_at' => now(),
            ]));
        }

        // Keep existing Maternity balances consistent with the new 10-day allocation.
        $maternityId = DB::table('leave_types')->where('code', 'ML')->value('id');

        if ($maternityId) {
            $balances = DB::table('employee_leave_balances')
                ->where('leave_type_id', $maternityId)
                ->get();

            foreach ($balances as $balance) {
                DB::table('employee_leave_balances')
                    ->where('id', $balance->id)
                    ->update([
                        'allocated' => 10,
                        'remaining' => max(10 - (float) $balance->used, 0),
                        'updated_at' => now(),
                    ]);
            }
        }

        // Backfill Paternity balances for Male employees on years they already have balances.
        $paternityId = DB::table('leave_types')->where('code', 'PTL')->value('id');

        if ($paternityId) {
            $years = DB::table('employee_leave_balances')->distinct()->pluck('year');

            $employees = DB::table('employees')
                ->when(Schema::hasColumn('employees', 'deleted_at'), fn ($q) => $q->whereNull('deleted_at'))
                ->where('gender', 'Male')
                ->pluck('id');

            foreach ($employees as $employeeId) {
                foreach ($years as $year) {
                    $hasAnyBalance = DB::table('employee_leave_balances')
                        ->where('employee_id', $employeeId)
                        ->where('year', $year)
                        ->exists();

                    if (!$hasAnyBalance) {
                        continue;
                    }

                    $hasPaternity = DB::table('employee_leave_balances')
                        ->where('employee_id', $employeeId)
                        ->where('leave_type_id', $paternityId)
                        ->where('year', $year)
                        ->exists();

                    if ($hasPaternity) {
                        continue;
                    }

                    DB::table('employee_leave_balances')->insert([
                        'employee_id' => $employeeId,
                        'leave_type_id' => $paternityId,
                        'allocated' => 10,
                        'used' => 0,
                        'remaining' => 10,
                        'carry_forward' => 0,
                        'expired' => 0,
                        'year' => $year,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        DB::table('leave_types')->where('code', 'PTL')->delete();

        if (Schema::hasColumn('leave_types', 'gender_restriction')) {
            Schema::table('leave_types', function (Blueprint $table) {
                $table->dropColumn('gender_restriction');
            });
        }
    }
};
