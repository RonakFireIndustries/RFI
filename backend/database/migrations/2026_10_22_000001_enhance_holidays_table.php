<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Turns the bare `holidays` scaffold into the company holiday calendar.
 *
 *  - adds `description` (notes about the closure: which offices, what it
 *    covers, who to contact);
 *  - normalises the free-text `type` / `status` columns onto the canonical
 *    vocabulary the API, the report pack and the payroll/attendance readers
 *    now share, so an older row can never sit on a value nothing matches.
 */
return new class extends Migration
{
    /**
     * Legacy value => canonical value.
     */
    private const TYPE_MAP = [
        'public' => 'Public Holiday',
        'public holiday' => 'Public Holiday',
        'national' => 'Public Holiday',
        'national holiday' => 'Public Holiday',
        'gazetted' => 'Public Holiday',
        'gazetted holiday' => 'Public Holiday',
        'restricted' => 'Restricted Holiday',
        'restricted holiday' => 'Restricted Holiday',
        'optional' => 'Optional Holiday',
        'optional holiday' => 'Optional Holiday',
    ];

    private const STATUS_MAP = [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'disabled' => 'Inactive',
        'cancelled' => 'Inactive',
    ];

    public function up(): void
    {
        if (!Schema::hasColumn('holidays', 'description')) {
            Schema::table('holidays', function (Blueprint $table) {
                $table->text('description')->nullable()->after('status');
            });
        }

        $this->normalise('type', self::TYPE_MAP);
        $this->normalise('status', self::STATUS_MAP);
    }

    public function down(): void
    {
        if (Schema::hasColumn('holidays', 'description')) {
            Schema::table('holidays', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }
    }

    /**
     * Rewrite the legacy spellings of a column onto their canonical value.
     *
     * Done row by row rather than with a blanket UPDATE so that a value that
     * is already canonical is left untouched, and so a value that is neither
     * canonical nor a known alias falls back to the column default instead of
     * surviving as an orphan string.
     */
    private function normalise(string $column, array $map): void
    {
        $rows = DB::table('holidays')->select('id', $column)->get();

        foreach ($rows as $row) {
            $current = (string) $row->{$column};

            if ($column === 'status') {
                $canonical = $map[strtolower($current)] ?? 'Active';
            } else {
                $canonical = $map[strtolower($current)] ?? 'Public Holiday';
            }

            if ($canonical !== $current) {
                DB::table('holidays')->where('id', $row->id)->update([$column => $canonical]);
            }
        }
    }
};
