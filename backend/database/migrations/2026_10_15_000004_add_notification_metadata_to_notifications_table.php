<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Metadata for the notification catalogue in config/notifications.php.
     *
     *  - module      groups notifications in the UI and lets the list be filtered.
     *  - dedupe_key  lets the daily scan repeat a persistent alert (overdue
     *                invoice, low stock) without stacking copies on top of an
     *                unread one.
     *  - data        the placeholder values used to render the message, kept so
     *                a future rewrite can re-render old rows.
     *
     * Guarded per column: installs in the wild have had columns added by hand,
     * and this must be a no-op wherever one already exists.
     */
    public function up(): void
    {
        if (!Schema::hasTable('notifications')) {
            return;
        }

        $columns = [
            'module' => fn (Blueprint $t) => $t->string('module')->nullable()->after('type'),
            'dedupe_key' => fn (Blueprint $t) => $t->string('dedupe_key')->nullable()->after('module'),
            'data' => fn (Blueprint $t) => $t->json('data')->nullable()->after('link'),
        ];

        Schema::table('notifications', function (Blueprint $table) use ($columns) {
            foreach ($columns as $name => $definition) {
                if (!Schema::hasColumn('notifications', $name)) {
                    $definition($table);
                }
            }
        });

        // The daily scan queries on this constantly.
        if (Schema::hasColumn('notifications', 'dedupe_key')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->index(['user_id', 'dedupe_key'], 'notifications_user_dedupe_index');
            });
        }
    }

    /**
     * Dropping these would only lose grouping and de-duplication metadata, so
     * reversal is safe in principle - but re-adding on a rollback would race
     * with an install that hand-added the same columns.
     */
    public function down(): void
    {
        //
    }
};
