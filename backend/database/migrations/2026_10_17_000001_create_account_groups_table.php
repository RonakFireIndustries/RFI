<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tally-style account groups. Every ledger belongs to a group, and a group
     * belongs to one of four natures (Assets / Liabilities / Income / Expenses)
     * which decides how it is presented in the Balance Sheet and P&L.
     *
     * Primary groups (is_primary) are the 28 Tally defaults; sub-groups hang off
     * them through parent_id. `affects_gross_profit` mirrors Tally's
     * direct (true) vs indirect (false) split for income/expense groups.
     */
    public function up(): void
    {
        if (Schema::hasTable('account_groups')) {
            return;
        }

        Schema::create('account_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->enum('nature', ['Assets', 'Liabilities', 'Income', 'Expenses']);
            $table->boolean('is_primary')->default(false);
            $table->boolean('affects_gross_profit')->default(false);
            // Sundry Debtors / Sundry Creditors are party groups: the ledgers
            // inside them represent customers or suppliers.
            $table->boolean('is_party_group')->default(false);
            $table->string('party_type')->nullable(); // customer | supplier
            // System groups are seeded defaults and cannot be deleted.
            $table->boolean('is_system')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique('name');
        });

        Schema::table('account_groups', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('account_groups')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_groups');
    }
};
