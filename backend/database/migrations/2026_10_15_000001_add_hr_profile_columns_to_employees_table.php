<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Statutory and banking details for an employee.
     *
     * The payslip already renders pf_number, uan, esic_number and
     * bank_account_number, but no screen existed to capture them and no
     * migration ever created the columns - so those lines always read "-".
     *
     * Every column is guarded: the `employees` table has drifted ahead of its
     * migrations on some installs (columns added by hand), and this must be a
     * no-op wherever a column already exists.
     */
    public function up(): void
    {
        if (!Schema::hasTable('employees')) {
            return;
        }

        $columns = [
            // Blood group - used by HR in emergencies.
            'blood_group' => fn (Blueprint $t) => $t->string('blood_group')->nullable(),

            // Bank details - where salary is credited.
            'account_holder_name' => fn (Blueprint $t) => $t->string('account_holder_name')->nullable(),
            'bank_name' => fn (Blueprint $t) => $t->string('bank_name')->nullable(),
            'bank_branch' => fn (Blueprint $t) => $t->string('bank_branch')->nullable(),
            // IFSC is 11 chars; account numbers keep leading zeros, so string.
            'bank_ifsc' => fn (Blueprint $t) => $t->string('bank_ifsc', 20)->nullable(),
            'bank_account_number' => fn (Blueprint $t) => $t->string('bank_account_number', 30)->nullable(),

            // PF details - the payslip shows both the PF account no. and UAN.
            'pf_number' => fn (Blueprint $t) => $t->string('pf_number')->nullable(),
            'uan' => fn (Blueprint $t) => $t->string('uan')->nullable(),

            // ESIC details.
            'esic_number' => fn (Blueprint $t) => $t->string('esic_number')->nullable(),
        ];

        Schema::table('employees', function (Blueprint $table) use ($columns) {
            foreach ($columns as $name => $definition) {
                if (!Schema::hasColumn('employees', $name)) {
                    $definition($table);
                }
            }
        });
    }

    /**
     * Deliberately not reversible: dropping these would discard bank and
     * statutory data entered since the columns were added.
     */
    public function down(): void
    {
        //
    }
};
