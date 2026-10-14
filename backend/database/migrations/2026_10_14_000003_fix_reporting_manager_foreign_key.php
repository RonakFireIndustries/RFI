<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repoint employees.reporting_manager_id at `employees` instead of `users`.
     *
     * 2026_06_13_075512 created the column with ->constrained('users'), but every
     * reader treats it as an employee id: Employee::manager() is a belongsTo on
     * employees, Store/UpdateEmployeeRequest validate exists:employees,id, and the
     * UI dropdown is populated from /employees. The mismatch either rejects valid
     * writes with a foreign key error or silently resolves to the wrong person.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('employees', 'reporting_manager_id')) {
            return;
        }

        // 1. Drop the existing constraint first: repointing rows while a foreign
        //    key to `users` is still live would fail for any employee id that has
        //    no matching user.
        $foreignKey = $this->findForeignKey();
        if ($foreignKey !== null) {
            Schema::table('employees', function (Blueprint $table) use ($foreignKey) {
                $table->dropForeign($foreignKey);
            });
        }

        // 2. Clear/repair rows that do not resolve to an employee, otherwise the
        //    corrected constraint cannot be created.
        $this->repointOrphanedValues();

        // 3. Point the column at the table the application actually reads.
        Schema::table('employees', function (Blueprint $table) {
            $table->foreign('reporting_manager_id')
                ->references('id')
                ->on('employees')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('employees', 'reporting_manager_id')) {
            return;
        }

        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['reporting_manager_id']);
        });

        if (!$this->isMySql()) {
            return;
        }

        // Only restore the original users reference when every value still
        // resolves there; otherwise restoring it would fail the rollback.
        $unresolvable = DB::table('employees as employee')
            ->leftJoin('users as user', 'user.id', '=', 'employee.reporting_manager_id')
            ->whereNotNull('employee.reporting_manager_id')
            ->whereNull('user.id')
            ->exists();

        if ($unresolvable) {
            Log::warning(
                'Migration rollback left employees.reporting_manager_id without a '
                . 'foreign key: some values no longer resolve to a row in users.'
            );

            return;
        }

        Schema::table('employees', function (Blueprint $table) {
            $table->foreign('reporting_manager_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Resolve rows whose value does not exist in employees: relink to the
     * employee account created from that user where one exists (the value was
     * written as a user id), otherwise null it out. The relation already
     * returned null for these rows, so nothing visible is lost.
     */
    private function repointOrphanedValues(): void
    {
        $orphans = DB::table('employees as employee')
            ->leftJoin('employees as manager', 'manager.id', '=', 'employee.reporting_manager_id')
            ->whereNotNull('employee.reporting_manager_id')
            ->whereNull('manager.id')
            ->get(['employee.id', 'employee.reporting_manager_id']);

        if ($orphans->isEmpty()) {
            return;
        }

        $relinked = 0;

        foreach ($orphans as $row) {
            $linked = DB::table('employees')
                ->where('user_id', $row->reporting_manager_id)
                ->value('id');

            DB::table('employees')
                ->where('id', $row->id)
                ->update(['reporting_manager_id' => $linked]);

            if ($linked !== null) {
                $relinked++;
            }
        }

        Log::warning('Repaired employees.reporting_manager_id values that did not resolve to an employee.', [
            'total' => $orphans->count(),
            'relinked_to_employee_by_user' => $relinked,
            'cleared' => $orphans->count() - $relinked,
        ]);
    }

    /**
     * Name of whichever foreign key currently sits on the column; the name
     * differs depending on whether the column came from a migration or a dump.
     */
    private function findForeignKey(): ?string
    {
        if (!$this->isMySql()) {
            return null;
        }

        $row = DB::selectOne(
            'SELECT CONSTRAINT_NAME AS name
               FROM information_schema.KEY_COLUMN_USAGE
              WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = ?
                AND COLUMN_NAME = ?
                AND REFERENCED_TABLE_NAME IS NOT NULL
              LIMIT 1',
            ['employees', 'reporting_manager_id']
        );

        return $row->name ?? null;
    }

    private function isMySql(): bool
    {
        return in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true);
    }
};
