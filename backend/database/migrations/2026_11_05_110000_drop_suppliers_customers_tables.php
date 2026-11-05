<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Retire the supplier / customer masters now that parties are ledgers.
     *
     * Order of operations:
     *   1. Give ledger_accounts the contact / terms columns the masters held,
     *      so no email, phone, address or credit days is lost.
     *   2. Add a `<party>_ledger_id` column next to every old FK column, point
     *      it at the converted ledger (matched on party_type + party_id), then
     *      drop the old FK column.
     *   3. Drop anything still referencing the two masters and finally drop
     *      the masters themselves.
     *
     * The 2026_11_05_100000 conversion migration must run first: it is what
     * creates a ledger (and the party_type + party_id bridge) for every row.
     * If it did not run (e.g. an empty install), the new columns are simply
     * left NULL and the tables are still safe to drop.
     */
    private const PARTY_COLUMNS = [
        'purchase_orders' => [
            ['supplier_id', 'supplier_ledger_id', 'supplier'],
        ],
        'sales_orders' => [
            ['customer_id', 'customer_ledger_id', 'customer'],
        ],
        'invoices' => [
            ['customer_id', 'customer_ledger_id', 'customer'],
        ],
        'payments' => [
            ['supplier_id', 'supplier_ledger_id', 'supplier'],
            ['customer_id', 'customer_ledger_id', 'customer'],
        ],
        'products' => [
            ['supplier_id', 'supplier_ledger_id', 'supplier'],
        ],
        'cash_vouchers' => [
            ['supplier_id', 'supplier_ledger_id', 'supplier'],
        ],
        'supplier_quotations' => [
            ['supplier_id', 'supplier_ledger_id', 'supplier'],
        ],
        'purchase_returns' => [
            ['supplier_id', 'supplier_ledger_id', 'supplier'],
        ],
        'customer_quotations' => [
            ['customer_id', 'customer_ledger_id', 'customer'],
        ],
        'sales_returns' => [
            ['customer_id', 'customer_ledger_id', 'customer'],
        ],
    ];

    public function up(): void
    {
        $this->addContactColumnsToLedgers();
        $this->backfillLedgerContacts();

        foreach (self::PARTY_COLUMNS as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as [$oldColumn, $newColumn, $partyType]) {
                $this->repointPartyColumn($table, $oldColumn, $newColumn, $partyType);
            }
        }

        // Safety net: an install may carry an extra FK we don't know about.
        // Dropping these is what makes the master tables droppable.
        $this->dropForeignKeysReferencing(['suppliers', 'customers']);

        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('customers');
    }

    public function down(): void
    {
        // Best effort: the old code is only restorable if the ledgers still
        // carry party_id. Parties added after this migration (party_id NULL)
        // cannot be recreated here; restore them from the pre-migration
        // backup instead.
        $this->recreateMasterTables();
        $this->restoreMasterRowsFromLedgers();

        foreach (self::PARTY_COLUMNS as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as [$oldColumn, $newColumn, $partyType]) {
                $this->restorePartyColumn($table, $oldColumn, $newColumn, $partyType);
            }
        }

        $this->dropColumnsSafely('ledger_accounts', ['credit_days', 'address', 'phone', 'email']);
    }

    /* --------------------------------------------------------------------- */
    /* Contact / terms data                                                   */
    /* --------------------------------------------------------------------- */

    private function addContactColumnsToLedgers(): void
    {
        if (! Schema::hasTable('ledger_accounts')) {
            return;
        }

        Schema::table('ledger_accounts', function (Blueprint $table) {
            if (! Schema::hasColumn('ledger_accounts', 'email')) {
                $table->string('email')->nullable()->after('name');
            }
            if (! Schema::hasColumn('ledger_accounts', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (! Schema::hasColumn('ledger_accounts', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }
            if (! Schema::hasColumn('ledger_accounts', 'credit_days')) {
                $table->unsignedSmallInteger('credit_days')->nullable()->after('address');
            }
        });
    }

    private function backfillLedgerContacts(): void
    {
        if (! Schema::hasTable('ledger_accounts')) {
            return;
        }

        $masters = [
            'supplier' => 'suppliers',
            'customer' => 'customers',
        ];

        foreach ($masters as $partyType => $master) {
            if (! Schema::hasTable($master) || ! Schema::hasColumn($master, 'name')) {
                continue;
            }

            $sets = [
                'l.email = COALESCE(l.email, m.email)',
                'l.phone = COALESCE(l.phone, m.phone)',
                'l.address = COALESCE(l.address, m.address)',
            ];

            if (Schema::hasColumn($master, 'credit_days')) {
                $sets[] = 'l.credit_days = COALESCE(l.credit_days, m.credit_days)';
            }

            DB::statement(
                'UPDATE `ledger_accounts` l '.
                "JOIN `{$master}` m ON m.id = l.party_id ".
                'SET '.implode(', ', $sets).' '.
                'WHERE l.party_type = ?',
                [$partyType]
            );
        }
    }

    /* --------------------------------------------------------------------- */
    /* FK repointing                                                         */
    /* --------------------------------------------------------------------- */

    private function repointPartyColumn(
        string $table,
        string $oldColumn,
        string $newColumn,
        string $partyType
    ): void {
        $hasOld = Schema::hasColumn($table, $oldColumn);
        $hasNew = Schema::hasColumn($table, $newColumn);

        if (! $hasOld && ! $hasNew) {
            return;
        }

        if (! $hasNew) {
            Schema::table($table, function (Blueprint $t) use ($newColumn, $oldColumn) {
                $t->unsignedBigInteger($newColumn)->nullable()->after($oldColumn);
            });
        }

        $master = $partyType === 'supplier' ? 'suppliers' : 'customers';

        if ($hasOld && Schema::hasTable($master) && Schema::hasTable('ledger_accounts')) {
            // The conversion migration links each ledger back with
            // party_type + party_id, so this is a precise hand-off.
            DB::statement(
                "UPDATE `{$table}` t ".
                "JOIN `{$master}` m ON m.id = t.`{$oldColumn}` ".
                'JOIN `ledger_accounts` l ON l.party_type = ? AND l.party_id = m.id '.
                "SET t.`{$newColumn}` = l.id",
                [$partyType]
            );
        }

        if ($hasOld) {
            $this->dropColumnSafely($table, $oldColumn);
        }

        $this->ensureLedgerForeignKey($table, $newColumn);
    }

    private function ensureLedgerForeignKey(string $table, string $column): void
    {
        if (! Schema::hasTable('ledger_accounts')) {
            return;
        }

        if ($this->hasForeignKeyOn($table, $column, 'ledger_accounts')) {
            return;
        }

        Schema::table($table, function (Blueprint $t) use ($column) {
            $t->foreign($column)
                ->references('id')
                ->on('ledger_accounts')
                ->nullOnDelete();
        });
    }

    private function hasForeignKeyOn(string $table, string $column, string $referenced): bool
    {
        if (DB::getDriverName() !== 'mysql') {
            return false;
        }

        return (bool) DB::selectOne(
            'SELECT 1 FROM information_schema.KEY_COLUMN_USAGE '.
            'WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? '.
            'AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME = ? LIMIT 1',
            [$table, $column, $referenced]
        );
    }

    private function dropForeignKeysReferencing(array $tables): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $placeholders = implode(', ', array_fill(0, count($tables), '?'));

        $rows = DB::select(
            'SELECT TABLE_NAME, CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE '.
            'WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME IN ('.$placeholders.')',
            $tables
        );

        foreach ($rows as $row) {
            DB::statement(
                "ALTER TABLE `{$row->TABLE_NAME}` DROP FOREIGN KEY `{$row->CONSTRAINT_NAME}`"
            );
        }
    }

    /* --------------------------------------------------------------------- */
    /* Column helpers                                                        */
    /* --------------------------------------------------------------------- */

    /**
     * Drop a column on MariaDB without tripping over the index it sits in.
     *
     * Mirrors 2026_06_20_174233_remove_branches.php: foreign keys built on the
     * column are dropped for good, unique indexes covering it are dropped and
     * recreated without it, and any FK that merely shared the index is put
     * back afterwards.
     */
    private function dropColumnSafely(string $table, string $column): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            Schema::table($table, fn (Blueprint $t) => $t->dropColumn($column));

            return;
        }

        $fkRows = DB::select(
            'SELECT kcu.CONSTRAINT_NAME, kcu.REFERENCED_TABLE_NAME, '.
            '       kcu.COLUMN_NAME AS col, kcu.REFERENCED_COLUMN_NAME AS ref_col, '.
            '       kcu.ORDINAL_POSITION, rc.UPDATE_RULE, rc.DELETE_RULE '.
            '  FROM information_schema.KEY_COLUMN_USAGE kcu '.
            '  JOIN information_schema.REFERENTIAL_CONSTRAINTS rc '.
            '    ON rc.CONSTRAINT_SCHEMA = kcu.CONSTRAINT_SCHEMA '.
            '   AND rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME '.
            '   AND rc.TABLE_NAME = kcu.TABLE_NAME '.
            ' WHERE kcu.TABLE_SCHEMA = DATABASE() '.
            '   AND kcu.TABLE_NAME = ? '.
            '   AND kcu.REFERENCED_TABLE_NAME IS NOT NULL '.
            ' ORDER BY kcu.CONSTRAINT_NAME, kcu.ORDINAL_POSITION',
            [$table]
        );

        $foreignKeys = [];
        foreach ($fkRows as $row) {
            $name = $row->CONSTRAINT_NAME;
            if (! isset($foreignKeys[$name])) {
                $foreignKeys[$name] = [
                    'name' => $name,
                    'ref_table' => $row->REFERENCED_TABLE_NAME,
                    'cols' => [],
                    'ref_cols' => [],
                    'update_rule' => $row->UPDATE_RULE,
                    'delete_rule' => $row->DELETE_RULE,
                ];
            }
            $foreignKeys[$name]['cols'][] = $row->col;
            $foreignKeys[$name]['ref_cols'][] = $row->ref_col;
        }

        $tableIndexes = [];
        $statRows = DB::select(
            'SELECT INDEX_NAME, COLUMN_NAME FROM information_schema.STATISTICS '.
            ' WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? '.
            ' ORDER BY INDEX_NAME, SEQ_IN_INDEX',
            [$table]
        );
        foreach ($statRows as $stat) {
            $tableIndexes[$stat->INDEX_NAME][] = $stat->COLUMN_NAME;
        }

        // MariaDB refuses DROP COLUMN while a unique index still covers it.
        $indexNames = [];
        $indexColumns = [];
        foreach ($tableIndexes as $name => $cols) {
            if ($name === 'PRIMARY' || ! in_array($column, $cols, true)) {
                continue;
            }

            $nonUnique = DB::select(
                'SELECT NON_UNIQUE FROM information_schema.STATISTICS '.
                ' WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1',
                [$table, $name]
            );

            if (! empty($nonUnique) && (int) $nonUnique[0]->NON_UNIQUE === 0) {
                $indexNames[] = $name;
                $indexColumns[$name] = $cols;
            }
        }

        $dependentFks = [];
        $columnFks = [];
        foreach ($foreignKeys as $fk) {
            if (in_array($column, $fk['cols'], true)) {
                $columnFks[] = $fk;

                continue;
            }

            foreach ($tableIndexes as $indexName => $indexCols) {
                if (array_slice($indexCols, 0, count($fk['cols'])) === $fk['cols']) {
                    if (in_array($indexName, $indexNames, true)) {
                        $dependentFks[] = $fk;
                    }
                    break;
                }
            }
        }

        foreach (array_merge($columnFks, $dependentFks) as $fk) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fk['name']}`");
        }

        foreach ($indexNames as $name) {
            DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$name}`");
        }

        Schema::table($table, fn (Blueprint $t) => $t->dropColumn($column));

        foreach ($indexColumns as $name => $cols) {
            $remaining = array_values(array_diff($cols, [$column]));
            if (empty($remaining)) {
                continue;
            }

            $columnsSql = implode(', ', array_map(fn ($c) => "`{$c}`", $remaining));
            DB::statement("ALTER TABLE `{$table}` ADD UNIQUE INDEX `{$name}` ({$columnsSql})");
        }

        foreach ($dependentFks as $fk) {
            $rules = '';
            if ($fk['update_rule'] && $fk['update_rule'] !== 'NO ACTION') {
                $rules .= " ON UPDATE {$fk['update_rule']}";
            }
            if ($fk['delete_rule'] && $fk['delete_rule'] !== 'NO ACTION') {
                $rules .= " ON DELETE {$fk['delete_rule']}";
            }

            $cols = implode(', ', array_map(fn ($c) => "`{$c}`", $fk['cols']));
            $refCols = implode(', ', array_map(fn ($c) => "`{$c}`", $fk['ref_cols']));
            DB::statement(
                "ALTER TABLE `{$table}` ADD CONSTRAINT `{$fk['name']}` ".
                "FOREIGN KEY ({$cols}) REFERENCES `{$fk['ref_table']}` ({$refCols}){$rules}"
            );
        }
    }

    private function dropColumnsSafely(string $table, array $columns): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column)) {
                $this->dropColumnSafely($table, $column);
            }
        }
    }

    /* --------------------------------------------------------------------- */
    /* Rollback                                                              */
    /* --------------------------------------------------------------------- */

    private function recreateMasterTables(): void
    {
        if (! Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('gst_number')->nullable();
                $table->text('address')->nullable();
                $table->boolean('is_system')->default(false);
                $table->unsignedSmallInteger('credit_days')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('gst_number')->nullable();
                $table->text('address')->nullable();
                $table->unsignedSmallInteger('credit_days')->nullable();
                $table->timestamps();
            });
        }
    }

    private function restoreMasterRowsFromLedgers(): void
    {
        if (! Schema::hasTable('ledger_accounts')) {
            return;
        }

        $now = now();

        $rows = DB::table('ledger_accounts')
            ->whereNotNull('party_id')
            ->whereIn('party_type', ['supplier', 'customer'])
            ->get();

        foreach ($rows as $ledger) {
            $table = $ledger->party_type === 'supplier' ? 'suppliers' : 'customers';

            if (DB::table($table)->where('id', $ledger->party_id)->exists()) {
                continue;
            }

            DB::table($table)->insert([
                'id' => $ledger->party_id,
                'name' => $ledger->name,
                'email' => $ledger->email ?? null,
                'phone' => $ledger->phone ?? null,
                'gst_number' => $ledger->gst_number,
                'address' => $ledger->address ?? null,
                'credit_days' => $ledger->credit_days ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ] + ($table === 'suppliers' ? ['is_system' => false] : []));
        }
    }

    private function restorePartyColumn(
        string $table,
        string $oldColumn,
        string $newColumn,
        string $partyType
    ): void {
        $hasOld = Schema::hasColumn($table, $oldColumn);
        $hasNew = Schema::hasColumn($table, $newColumn);

        if (! $hasNew && ! $hasOld) {
            return;
        }

        if (! $hasOld) {
            Schema::table($table, function (Blueprint $t) use ($oldColumn, $newColumn) {
                $t->unsignedBigInteger($oldColumn)->nullable()->after($newColumn);
            });
        }

        if ($hasNew && Schema::hasTable('ledger_accounts')) {
            DB::statement(
                "UPDATE `{$table}` t ".
                'JOIN `ledger_accounts` l ON l.id = t.`'.$newColumn.'` '.
                "SET t.`{$oldColumn}` = l.party_id ".
                'WHERE l.party_type = ?',
                [$partyType]
            );
        }

        if ($hasNew) {
            $this->dropColumnSafely($table, $newColumn);
        }
    }
};
