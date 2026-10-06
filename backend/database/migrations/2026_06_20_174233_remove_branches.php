<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $branchIdTables = [
        'attendances', 'categories', 'invoices', 'inventories', 'shifts',
        'payrolls', 'purchase_orders', 'sales_orders', 'delivery_notes',
        'goods_receipt_notes', 'payments', 'suppliers', 'customers', 'tasks',
    ];

    /**
     * Drop a column safely on MariaDB builds where DROP COLUMN fails with
     * ER_KEY_COLUMN_DOESNT_EXITS (1072) while a unique index still covers it.
     * Dependent foreign keys and the unique indexes themselves are dropped and
     * recreated (minus the removed column) afterwards.
     */
    private function dropColumnSafely(string $table, string $column): void
    {
        $fkRows = DB::select(
            "SELECT kcu.CONSTRAINT_NAME, kcu.REFERENCED_TABLE_NAME,
                    kcu.COLUMN_NAME AS col, kcu.REFERENCED_COLUMN_NAME AS ref_col,
                    kcu.ORDINAL_POSITION, rc.UPDATE_RULE, rc.DELETE_RULE
               FROM information_schema.KEY_COLUMN_USAGE kcu
               JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
                 ON rc.CONSTRAINT_SCHEMA = kcu.CONSTRAINT_SCHEMA
                AND rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
                AND rc.TABLE_NAME = kcu.TABLE_NAME
              WHERE kcu.TABLE_SCHEMA = DATABASE()
                AND kcu.TABLE_NAME = ?
                AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
              ORDER BY kcu.CONSTRAINT_NAME, kcu.ORDINAL_POSITION",
            [$table]
        );

        $foreignKeys = [];
        foreach ($fkRows as $row) {
            $name = $row->CONSTRAINT_NAME;
            if (!isset($foreignKeys[$name])) {
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

        // MariaDB's KEY_COLUMN_USAGE has no INDEX_NAME, so map each foreign key
        // to the index whose leading columns match its own columns.
        $tableIndexes = [];
        $statRows = DB::select(
            "SELECT INDEX_NAME, COLUMN_NAME FROM information_schema.STATISTICS
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
              ORDER BY INDEX_NAME, SEQ_IN_INDEX",
            [$table]
        );
        foreach ($statRows as $stat) {
            $tableIndexes[$stat->INDEX_NAME][] = $stat->COLUMN_NAME;
        }

        // MariaDB fails to DROP COLUMN while a unique index still covers it,
        // so unique indexes containing the column must go first.
        $indexNames = [];
        $indexColumns = [];
        foreach ($tableIndexes as $name => $cols) {
            if ($name !== 'PRIMARY' && in_array($column, $cols, true)) {
                $nonUnique = DB::select(
                    "SELECT NON_UNIQUE FROM information_schema.STATISTICS
                      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?
                      LIMIT 1",
                    [$table, $name]
                );
                if (!empty($nonUnique) && (int) $nonUnique[0]->NON_UNIQUE === 0) {
                    $indexNames[] = $name;
                    $indexColumns[$name] = $cols;
                }
            }
        }

        $dependentFks = [];
        $columnFks = [];
        foreach ($foreignKeys as $fk) {
            if (in_array($column, $fk['cols'], true)) {
                // The foreign key itself is built on the removed column
                // (e.g. branch_id -> branches): drop it for good.
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
            if (!empty($remaining)) {
                $columnsSql = implode(', ', array_map(fn ($c) => "`{$c}`", $remaining));
                DB::statement("ALTER TABLE `{$table}` ADD UNIQUE INDEX `{$name}` ({$columnsSql})");
            }
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
                "ALTER TABLE `{$table}` ADD CONSTRAINT `{$fk['name']}` " .
                "FOREIGN KEY ({$cols}) REFERENCES `{$fk['ref_table']}` ({$refCols}){$rules}"
            );
        }
    }

    public function up(): void
    {
        // Drop foreign keys then columns
        foreach ($this->branchIdTables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'branch_id')) continue;
            $this->dropColumnSafely($table, 'branch_id');
        }

        // inventory_transfers has source_branch_id and destination_branch_id
        if (Schema::hasTable('inventory_transfers')) {
            foreach (['source_branch_id', 'destination_branch_id'] as $col) {
                if (!Schema::hasColumn('inventory_transfers', $col)) continue;
                $this->dropColumnSafely('inventory_transfers', $col);
            }
        }

        // Drop any remaining foreign keys referencing branches (e.g. users,
        // employees keep their branch_id columns as plain integers). The
        // orphaned columns become nullable so later inserts without a branch
        // keep working.
        $referencingFks = DB::select(
            "SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME
               FROM information_schema.KEY_COLUMN_USAGE
              WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME = 'branches'"
        );
        foreach ($referencingFks as $fk) {
            DB::statement("ALTER TABLE `{$fk->TABLE_NAME}` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");

            $column = DB::selectOne(
                "SELECT COLUMN_TYPE, IS_NULLABLE FROM information_schema.COLUMNS
                  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
                [$fk->TABLE_NAME, $fk->COLUMN_NAME]
            );
            if ($column && $column->IS_NULLABLE === 'NO') {
                DB::statement(
                    "ALTER TABLE `{$fk->TABLE_NAME}` MODIFY `{$fk->COLUMN_NAME}` {$column->COLUMN_TYPE} NULL"
                );
            }
        }

        // Drop branches table
        Schema::dropIfExists('branches');
    }

    public function down(): void
    {
        // Recreate branches table
        if (!Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('location')->nullable();
                $table->timestamps();
            });
        }
    }
};
